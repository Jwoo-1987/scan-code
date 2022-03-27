<?php
namespace App\Common\Util;

/**
 * 分页类
 * Class Paginate
 * @package App\Common\Util
 */
class Paginate
{
    /**
     * 起始行数
     * @var int|mixed
     */
    public $first_row;

    /**
     * 列表每页显示行数
     * @var int|mixed
     */
    public $list_rows;

    /**
     * 总页数
     * @var float
     */
    protected $total_pages;

    /**
     * 总行数
     * @var mixed
     */
    protected $total_rows;

    /**
     * 当前页数
     * @var float
     */
    protected $now_page;

    /**
     * 处理情况 ajax分页 html分页(静态化时) 普通get方式
     * @var mixed|string
     */
    protected $method = 'html';

    /**
     * 链接参数
     * @var mixed|string
     */
    protected $parameter = '';

    /**
     * 分页参数名称
     * @var mixed|string
     */
    protected $page_name;

    /**
     * ajax分页链接处理JS函数
     * @var mixed|string
     */
    protected $ajax_func_name;

    /**
     * 分页偏移量
     * @var int
     */
    public $plus = 3;

    /**
     * @var
     */
    protected $url;

    /**
     * 构造函数
     * @param array $data
     */
    public function __construct($data = array())
    {
        $this->total_rows = $data['total_rows'];

        $this->parameter = !empty($data['parameter']) ? $data['parameter'] : '';
        $this->list_rows = !empty($data['list_rows']) && $data['list_rows'] <= 100 ? $data['list_rows'] : 15;
        $this->total_pages = ceil($this->total_rows / $this->list_rows);
        $this->page_name = !empty($data['page_name']) ? $data['page_name'] : 'p';
        $this->ajax_func_name = !empty($data['ajax_func_name']) ? $data['ajax_func_name'] : '';

        $this->method = !empty($data['method']) ? $data['method'] : '';


        /* 当前页面 */
        if (!empty($data['now_page'])) {
            $this->now_page = intval($data['now_page']);
        } else {
            $this->now_page = !empty($_GET[$this->page_name]) ? intval($_GET[$this->page_name]) : 1;
        }
        $this->now_page = $this->now_page <= 0 ? 1 : $this->now_page;


        if (!empty($this->total_pages) && $this->now_page > $this->total_pages) {
            $this->now_page = $this->total_pages;
        }
        $this->first_row = $this->list_rows * ($this->now_page - 1);
    }

    /**
     * 得到当前连接,空白样式的链接，去掉_blank，注释掉由样式的链接，就可以使用
     * @param $page
     * @param $text
     * @return string
     */
    protected function _get_link_blank($page, $text)
    {
        switch ($this->method) {
            case 'ajax':
                $parameter = '';
                if ($this->parameter) {
                    $parameter = ',' . $this->parameter;
                }
                return '<a class="item" onclick="' . $this->ajax_func_name . '(\'' . $page . '\'' . $parameter . ')" href="javascript:void(0)">' . $text . '</a>' . "\n";
                break;

            case 'html':
                $url = str_replace('?', $page, $this->parameter);
                return '<a class="item" href="' . $url . '">' . $text . '</a>' . "\n";
                break;

            default:
                return '<a class="item" href="' . $this->_get_url($page) . '">' . $text . '</a>' . "\n";
                break;
        }
    }

    /**
     * 有后台样式的跳转链接
     * @param $page
     * @param $text
     * @return string
     */
    protected function _get_link($page, $text)
    {
        switch ($this->method) {
            case 'ajax':
                $parameter = '';
                if ($this->parameter) {
                    $parameter = ",'" . $this->parameter."'";
                }
                if ($text == '<') {
                    return '<a class="item" onclick="' . $this->ajax_func_name . '(\'' . $page . '\'' . $parameter . ')" href="javascript:void(0)">' . $text . '</a>' . "\n";
                } elseif ($text == '>') {
                    return '<a class="item" onclick="' . $this->ajax_func_name . '(\'' . $page . '\'' . $parameter . ')" href="javascript:void(0)">' . $text . '</a>' . "\n";
                } else {
                    return '<a class="item" onclick="' . $this->ajax_func_name . '(\'' . $page . '\'' . $parameter . ')" href="javascript:void(0)">' . $text . '</a>' . "\n";
                }
                break;

            case 'html':
                $url = str_replace('_?', $page, $this->parameter);
                if ($text == '<') {
                    return '<a class="item" href="' . $url . '">' . $text . '</a>' . "\n";
                } elseif ($text == '>') {
                    return '<a class="item" href="' . $url . '">' . $text . '</a>' . "\n";
                } else {
                    return '<a class="item" href="' . $url . '">' . $text . '</a>' . "\n";
                }
                break;

            default:
                return '<a href="' . $this->_get_url($page) . '">' . $text . '</a>' . "\n";
                break;
        }
    }

    /**
     * 设置当前页面链接
     */
    protected function _set_url()
    {
        $url = $_SERVER['REQUEST_URI'] . (strpos($_SERVER['REQUEST_URI'], '?') ? '' : "?") . $this->parameter;
        $parse = parse_url($url);
        if (isset($parse['query'])) {
            parse_str($parse['query'], $params);
            unset($params[$this->page_name]);
            $url = $parse['path'] . '?' . http_build_query($params);
        }
        if (!empty($params)) {
            $url .= '&';
        }
        $this->url = $url;
    }

    /**
     * 得到$page的url
     * @param string $page 页面
     * @return string
     */
    protected function _get_url($page)
    {
        if ($this->url === NULL) {
            $this->_set_url();
        }

        return $this->url . $this->page_name . '=' . $page;
    }

    /**
     * 得到第一页
     * @param string $name
     * @return string
     */
    public function first_page($name = '<<')
    {
        if ($this->now_page > 5) {
            return $this->_get_link('1', $name);
        }
        return '';
    }

    /**
     * 最后一页
     * @param string $name
     * @return string
     */
    public function last_page($name = '>>')
    {
        if ($this->now_page < $this->total_pages - 5) {
            return $this->_get_link($this->total_pages, $name);
        }
        return '';
    }

    /**
     * 上一页
     * @param string $name
     * @return string
     */
    public function up_page($name = '<')
    {
        if ($this->now_page != 1) {
            return $this->_get_link($this->now_page - 1, $name);
        }
        return '';
    }

    /**
     * 下一页
     * @param string $name
     * @return string
     */
    public function down_page($name = '>')
    {
        if ($this->now_page < $this->total_pages) {
            return $this->_get_link($this->now_page + 1, $name);
        }
        return '';
    }

    /**
     * 获取分页
     * @return string
     */
    public function show()
    {
        if ($this->total_pages != 1) {
            $return = '';
            $return .= $this->first_page('<<');
            $return .= $this->up_page('<');
            for ($i = 1; $i <= $this->total_pages; $i++) {
                if ($i == $this->now_page) {
                    $return .= "<a class='active item'>$i</a>\n";
                } else {
                    if ($this->now_page - $i >= 4 && $i != 1) {
                        $return .= "<div class=\"disabled item\">...</div>\n";
                        $i = $this->now_page - 3;
                    } else {
                        if ($i >= $this->now_page + 5 && $i != $this->total_pages) {
                            $return .= "<div class=\"disabled item\">...</div>\n";
                            $i = $this->total_pages;
                        }
                        $return .= $this->_get_link($i, $i) . "\n";
                    }
                }
            }
            $return .= $this->down_page('>');
            $return .= $this->last_page('>>');
            return $return;
        }
        return '';
    }

}