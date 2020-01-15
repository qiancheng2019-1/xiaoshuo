<?php

namespace App\Admin\Actions\Articles;

use App\Api\Basis\ReptileModel;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;

class Reptile extends Action
{
    public $name = '采集书本列表';

    protected $selector = '.reptile';

    public function handle(Request $request)
    {
        // $request ...
        $now = $request->input('now',1);
        $max = $request->input('max',1);

        if ($max > 100)
            return $this->response()->error('不建议采集次数超100次')->refresh();

        if ($now > $max)
            return $this->response()->success('已成功采集'.$max.'次')->refresh();

        $Reptile = new ReptileModel();
        $Reptile->getList();
//        if (!$Reptile->getList())
//            return $this->response()->error('爬虫错误');

        return $this->response()->success('已成功采集'.$now.'次,共采集'.$max.'次')->download($now);
    }

    public function form(){
        $this->integer('max','采集次数');
        $this->hidden('now','当前采集次数')->default(1);
    }

    public function html()
    {
        return <<<HTML
        <a class="btn btn-sm btn-success reptile"><i class="fa fa-book"></i>采集书本</a>
HTML;
    }

    //重写ajax回调处理方法
    public function handleActionPromise()
    {
        return <<<SCRIPT
        var actionResolver = function (data) {
            var response = data[0];
            var target   = data[1];

            if (typeof response !== 'object') {
                return $.admin.swal({type: 'error', title: 'Oops!'});
            }

            var then = function (then) {
                if (then.action == 'refresh') {
                    $.admin.reload();
                }

                if (then.action == 'download') {
                    setTimeout(repeatAjax(then.value),2000);
                }
            };

            if (typeof response.html === 'string') {
                target.html(response.html);
            }

            if (typeof response.swal === 'object') {
                $.admin.swal(response.swal);
            }

            if (typeof response.toastr === 'object' && response.toastr.type) {
                $.admin.toastr[response.toastr.type](response.toastr.content, '', response.toastr.options);
            }

            if (response.then) {
              then(response.then);
            }
        };

        var actionCatcher = function (request) {
            if (request && typeof request.responseJSON === 'object') {
                $.admin.toastr.error(request.responseJSON.message, '', {positionClass:"toast-bottom-center", timeOut: 10000}).css("width","500px")
            }
        };

        var repeatAjax = function(now){
            $('#'+modalId+' form input[name="now"]').val(parseInt(now)+1);
            var ccc = new Promise(function (resolve,reject) {
                Object.assign(data, {
                    _token: $.admin.token,
                    _action: '{$this->getCalledClass()}',
                });

                var formData = new FormData(form);
                for (var key in data) {
                    formData.append(key, data[key]);
                }

                $.ajax({
                    method: '{$this->getMethod()}',
                    url: '{$this->getHandleRoute()}',
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        resolve([data, target]);
                        if (data.status === true) {
                            $('#'+modalId).modal('hide');
                        }
                    },
                    error:function(request){
                        reject(request);
                    }
                });
            });
            ccc.then(actionResolver).catch(actionCatcher);
        };
        process.then(actionResolver).catch(actionCatcher);
SCRIPT;
    }
}
