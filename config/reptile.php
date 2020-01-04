<?php
return [
    'open'                        => 'yes',
    'break_pick'                  => 'yes',
    'proxy'                       => 'no',
    'pic_local'                   => 'yes',
    'pic_attr'                    => '',
    'name'                        => '37zw',
    'cate'                        => 'multi_cate',
    'domain'                      => 'https://www.35kushu.com',
    'url_replace'                 => 'www.35dxs.com###www.35kushu.com[line]www.kushuzw.com###www.35kushu.com[line]www.35zww.la###www.35kushu.com[line]',
    'charset'                     => 'GKB',
    'list_url'                    => 'https://www.35kushu.com/zwwsort{cate}/0/{page}.htm',
    'list_url_extra'              => '',
    'list_page'                   => '1|1|10',
    'list_cate'                   => [
        'k_1' => [
            'cate'     => '1',
            'ocate'    => '1',
            'max_page' => '749',],
        'k_2' => [
            'cate'     => '2',
            'ocate'    => '2',
            'max_page' => '288',],
        'k_3' => [
            'cate'     => '3',
            'ocate'    => '3',
            'max_page' => '1079',],
        'k_4' => [
            'cate'     => '4',
            'ocate'    => '4',
            'max_page' => '215',],
        'k_5' => [
            'cate'     => '5',
            'ocate'    => '5',
            'max_page' => '23',],
        'k_6' => [
            'cate'     => '6',
            'ocate'    => '6',
            'max_page' => '225',],
//        'k_7' => [
//            'cate'     => '7',
//            'ocate'    => '7',
//            'max_page' => '265',],
//        'k_8' => [
//            'cate'     => '8',
//            'ocate'    => '8',
//            'max_page' => '15',],
        ],
    'list_max_page'               => [
        0 => '558',
        1 => '220',
        2 => '934',
        3 => '170',
        4 => '180',
        5 => '218',
        6 => '161',
        7 => '161',
        8 => '',],
    'no_thumb_sign'               => 'no_cover',
    'list_selector_pre_filter'    => '',
    'list_selector'              => 'table td.odd a',
    'list_title_selector'        => 'table td.odd a',
    'list_thumb_selector'        => '',
    'list_author_selector'       => 'table td.odd:not([align]):not(:has("a"))',
    'view_selector_pre_filter'    => '{filter replace=\'1\'}玄幻小说{/filter}[line]{filter replace=\'2\'}武侠小说{/filter}[line]{filter replace=\'3\'}都市小说{/filter}[line]{filter replace=\'4\'}历史小说{/filter}[line]{filter replace=\'5\'}同人小说{/filter}[line]{filter replace=\'6\'}游戏小说{/filter}[line]{filter replace=\'7\'}科幻小说{/filter}[line]{filter replace=\'8\'}耽美小说{/filter}',
    'view_title_selector'         => 'h1',
    'view_author_selector'        => 'meta[property=og:novel:author]|content',
    'view_cate_selector'          => 'meta[property=og:novel:category]|content',
    'view_selector'              => 'meta[property=og:description]|content',
    'view_thumb_selector'         => 'meta[property=og:image]|content',
    'is_full_sign'                => '完本',
    'view_chapter_selector'       => '',
    'chapter_selector_pre_filter' => '',
    'chapter_area_selector'       => '#list dl',
    'chapter_regx'               => '<dd><a href="[link]" title="[title]">[string]</a></dd>',
    'chapter_order'              => 'yes',
    'chapter_order_num'           => '',
    'chapter_cont_pre_filter'      => '{filter replace=\'ｗww.wbｚd.org\'}ｗww.35ｚww.la{/filter}[line]{filter replace=\'wwｗ.ｗbzd.org\'}www.35zｗｗ.la{/filter}[line]{filter replace=\'com\'}Org{/filter}[line]{filter replace=\'\'}<div class="gg_read_content_up">(.*)</script></div>{/filter}[line]{filter replace=\'\'}三↑五↑中↑文↑网<br/><br/>{/filter}[line]{filter replace=\'\'}三↑五↑中↑文↑网{/filter}[line]{filter replace=\'xs8.me\'}35dxs.com{/filter}',
    'chapter_cont_selector'       => '#content',
    'chapter_cont_page_sign'       => '',
    'chapter_cont_page'           => '',
    'chapter_cont_par'            => '',];