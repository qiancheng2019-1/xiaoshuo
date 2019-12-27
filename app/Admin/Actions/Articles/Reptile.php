<?php

namespace App\Admin\Actions\Articles;

use Encore\Admin\Admin;
use App\V1\Basis\ReptileModel;
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


        if ($now >= $max)
            return $this->response()->success('已成功采集'.$now.'次')->refresh();
        return $this->response()->success('已成功采集'.$now.'次,共采集'.$max.'次')->download($now);

        $Reptile = new ReptileModel();
        $Reptile->getList();

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
        return <<<'SCRIPT'
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
                    $('.reptile input[name="now"]').value(then.value);
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

        Promise.all([
            process.then(actionResolver).catch(actionCatcher),
            process.then(actionResolver).catch(actionCatcher),
            process.then(actionResolver).catch(actionCatcher),
            process.then(actionResolver).catch(actionCatcher),
        ]);
SCRIPT;
    }
}
