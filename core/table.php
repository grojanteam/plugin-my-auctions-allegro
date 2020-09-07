<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Table extends WP_List_Table
{

    public $per_page = 20;

    public $search = 'Search';

    public $searchId = 'search_id';

    protected $singular;

    protected $object;

    protected $model;

    protected $helper;

    public function __construct()
    {
        parent::__construct(array(
            'singular' => $this->singular,
            'plural' => $this->object,
            'ajax' => false
        ));
    }

    public function show()
    {
        ob_start();
        $this->prepare_columns();
        $this->display();
        $content = ob_get_clean();

        return $content;
    }

    public function get_columns()
    {
        return [
            'cb' => '<input type="checkbox" />'
        ];
    }

    public function prepare_columns()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $this->setColumnHeaders(array(
            $columns,
            $hidden,
            $sortable
        ));
        $this->setItems($this->getData());
    }

    // public function get_bulk_actions()
    // {
    // return array(
    // 'delete' => __('Delete', 'your-textdomain'),
    // 'save' => __('Save', 'your-textdomain')
    // );
    // }
    public function setColumnHeaders($columns)
    {
        $this->_column_headers = $columns;
    }

    public function setItems($items)
    {
        $this->items = $items;
    }

    public function showSearch()
    {
        return false;
    }

    public function column_default($item, $column_name)
    {
        return $item[$column_name];
    }

    public function getHelper()
    {
        if (! $this->helper) {
            $this->helper = GJMAA::getHelper($this->object);
        }

        return $this->helper;
    }

    public function getModel()
    {
        if (! $this->model) {
            $this->model = GJMAA::getModel($this->object);
        }

        return $this->model;
    }

    public function getData()
    {
        $model = $this->getModel();
        $helper = $this->getHelper();
        $fields = $helper->getFieldsData('table');
        $result = [];

        $page = $this->get_pagenum();
        $limit = $this->per_page;

        $search = isset($_REQUEST['s']) ? $_REQUEST['s'] : [];

        $columns = array_keys($this->get_columns());

        $filters = $this->getFilters();
        $toFilter = [];

        foreach ($filters as $filter) {
            if (isset($_GET[$filter['id']]) && $_GET[$filter['id']] != '') {
                $toFilter[$filter['id']] = $_GET[$filter['id']];
            }
        }

        $this->set_pagination_args(array(
            'total_items' => $model->getCountFilteredResult($search, $columns, $toFilter),
            'per_page' => $limit
        ));

        $sort = $this->prepareSortFormat();

        foreach ($model->getFilteredResult($page, $limit, $search, $columns, $sort, $toFilter) as $value) {
            foreach ($this->get_columns() as $columnName => $label) {
                switch ($columnName) {
                    default:
                        $type = $fields[$columnName]['type'];
                        switch ($type) {
                            case 'select':
                                $source = GJMAA::getSource($fields[$columnName]['source'])->getAllOptions();
                                $singleResult = isset($value[$columnName]) && ! is_null($value[$columnName]) && isset($source[$value[$columnName]]) ? __($source[$value[$columnName]], GJMAA_TEXT_DOMAIN) : '';
                                break;
                            case 'json':
                                $singleResult = $this->parseJsonToProperView($value[$columnName]);
                                break;
                            default:
                                $singleResult = isset($value[$columnName]) && ! is_null($value[$columnName]) ? $value[$columnName] : '';
                                break;
                        }
                        $result[$value[$model->getDefaultPk()]][$columnName] = $singleResult;
                        break;
                    case 'cb':
                        break;
                    case 'action':
                        $actions = [];
                        foreach ($this->getActions() as $action => $label) {
                            $actions[] = sprintf('<a href="%s">%s</a>', admin_url('admin.php?page=' . $this->page . '&' . str_replace([
                                '{model_entity_id}',
                                '{model_entity_value_id}'
                            ], [
                                $model->getDefaultPk(),
                                $value[$model->getDefaultPk()]
                            ], $action)), __($label, GJMAA_TEXT_DOMAIN));
                        }
                        $result[$value[$model->getDefaultPk()]][$columnName] = implode(' | ', $actions);
                        break;
                }
            }
        }

        return $result;
    }

    public function prepareSortFormat()
    {
        $orderBy = null;
        $order = null;
        $sort = '';

        if (! empty($_GET['orderby'])) {
            $orderBy = $_GET['orderby'];
        }

        if (! empty($_GET['order'])) {
            $order = $_GET['order'];
        }

        if ($orderBy && $order) {
            $sort = sprintf('ORDER BY %s %s', $orderBy, strtoupper($order));
        }

        return $sort;
    }

    public function getCountData()
    {}

    public function getActions()
    {
        return $this->actions;
    }

    public function column_cb($item)
    {
        $model = GJMAA::getModel($this->object);

        return sprintf('<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item[$model->getDefaultPk()]);
    }

    public function parseJsonToProperView($json, $content = '')
    {
        $decoded = json_decode($json, true);

        if (is_array($decoded)) {
            foreach ($decoded as $label => $value) {
                if (is_array($value)) {
                    return $this->parseJsonToProperView(json_encode($value));
                } else {
                    $content .= $label . ': ' . $value . PHP_EOL;
                }
            }
        }
        return $content;
    }

    public function extra_tablenav($which)
    {
        if ($which == "top") :
            $this->renderNav($which);
        endif;

        if ($which == "bottom");
    }

    public function renderNav($which)
    {
        echo '<form id="gjmaa-form-'.$which.'" action="" method="GET">';
        $this->search_box(__('Search'), 'search-box-id');
        echo '<input type="hidden" name="page" value="' . $_REQUEST['page'] . '"/>';

        echo '<div class="tablenav top">';
        echo '<div class="alignleft actions bulkactions">';
        $filters = $this->getFilters();
        foreach ($filters as $filter) :
            $source = GJMAA::getSource($filter['source']);
            $options = $source->getOptions();
            if ($options) :
                echo '<select id="' . $filter['id'] . '_filter" name="' . $filter['id'] . '" class="gjmaa_filter">';
                echo '<option value="">' . __('Filter by', GJMAA_TEXT_DOMAIN) . ' ' . $filter['name'] . '</option>';
                foreach ($options as $value => $label) :
                    $selected = '';
                    if (isset($_GET[$filter['id']]) && $_GET[$filter['id']] != '' && $_GET[$filter['id']] == $value) :
                        $selected = ' selected = "selected"';
        endif;

                    echo '<option value="' . $value . '" ' . $selected . '>' . $label . '</option>';
                endforeach
                ;
                echo '</select>';
        endif;

        endforeach
        ;
        if (! empty($filters)) {
            echo '<input type="submit" class="button" value="' . __('Filter') . '" />';
        }
        echo '</div>';
        echo '</div>';
    }

    public function getFilters()
    {
        return [];
    }
}
?>