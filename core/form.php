<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Form
{

    protected $fields;

    protected $html = '';

    protected $inner_content = '';

    protected $values;

    protected $formId;
    
    protected $method = 'POST';

    public function prepareForm()
    {
        return $this;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function setFields($fields)
    {
        $this->fields = $fields;
        return $this;
    }

    public function generate($withFormContainer = true)
    {
        if ($withFormContainer)
            $this->startForm();
        $this->html .= '<table class="form-table">';
        foreach ($this->fields as $name => $fieldData) {
            $field = GJMAA::getFormField($fieldData['type']);
            if (null !== $this->getValues($name)) {
                $fieldData['value'] = $this->getValues($name);
            }
            $field->setInfo($fieldData);
            $class = $fieldData['type'] == 'hidden' ? ' class="hidden"' : '';
            $this->html .= '<tr' . $class . '><th scope="row">' . $field->toHtml() . '</td></tr>';
        }
        $this->displayInnerContent();
        $this->html .= '</table>';
        $this->html .= '<input type="hidden" name="redirect_url" value="' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '') .'" />';
        if ($withFormContainer)
            $this->endForm();

        return $this;
    }

    public function toHtml()
    {
        return $this->html;
    }

    public function addField($name, $fieldData)
    {
        $this->fields[$name] = $fieldData;
    }

    public function removeField($name)
    {
        if (isset($this->fields[$name])) {
            unset($this->fields[$name]);
        }
    }

    public function startForm()
    {
        $this->html .= '<form id="' . $this->getFormId() . '" name="' . $this->getFormName() . '" action="' . $this->getAction() . '" method="'.$this->getMethod().'">';
    }

    public function endForm()
    {
        $this->html .= '</form>';
    }

    public function getFormName()
    {
        return strtolower(get_class($this));
    }
    
    public function setAction($action)
    {
        $this->action = $action;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function setValues($values)
    {
        $this->values = $values;
        return $this;
    }

    public function getValues($key = null)
    {
        return $key ? (isset($this->values[$key]) ? $this->values[$key] : null) : $this->values;
    }

    public function displayInnerContent()
    {
        $this->html .= $this->getInnerContent();
    }

    public function getInnerContent()
    {
        return $this->inner_content;
    }

    public function setInnerContent($content)
    {
        $this->inner_content = $content;
    }

    public function getPageName()
    {
        return 'gjmaa_settings';
    }

    public function getFormId()
    {
        return $this->formId;
    }

    public function setFormId($formId)
    {
        $this->formId = $formId;
        return $this;
    }
    
    public function setMethod($method) 
    {
        $this->method = $method;
    }

    public function getMethod()
    {
        return $this->method;
    }
    
    
}