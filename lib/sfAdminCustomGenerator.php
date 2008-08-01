<?php
/**
 * Adds new functionality to the existing admin generator
 * - Mass actions
 * - Filters in header with mapping
 * To use add the following to generate.yml:
 * list:
 *   filter_location: header
 *   filter_location: seperate
 *   filter_header_map: {client_name: client_id}
 *   batch_actions:
 *     _deleteSelected:  ~
 *
 */
class sfAdminCustomGenerator extends sfExtjsPropelCrudGenerator
{
  /**
   * Returns HTML code for a column in edit mode.
   *
   * @param string  The column name
   * @param array   The parameters
   *
   * @return string HTML code
   */
  public function getColumnEditTag($column, $params = array())
  {
    // user defined parameters
    $user_params = $this->getParameterValue('edit.fields.'.$column->getName().'.params');
    $user_params = is_array($user_params) ? $user_params : sfToolkit::stringToArray($user_params);
    $params      = $user_params ? array_merge($params, $user_params) : $params;

    if ($column->isComponent())
    {
      return "get_component('".$this->getModuleName()."', '".$column->getName()."', array('type' => 'edit', '{$this->getSingularName()}' => \${$this->getSingularName()}))";
    }
    else if ($column->isPartial())
    {
      return "get_partial('".$column->getName()."', array('type' => 'edit', '{$this->getSingularName()}' => \${$this->getSingularName()}))";
    }

    // default control name
    $params = array_merge(array('control_name' => $this->getSingularName().'['.$column->getName().']'), $params);

    // default parameter values
    $type = $column->getCreoleType();
    if ($type == CreoleTypes::DATE)
    {
      $params = array_merge(array('rich' => true, 'calendar_button_img' => sfConfig::get('sf_admin_web_dir').'/images/date.png'), $params);
    }
    else if ($type == CreoleTypes::TIMESTAMP)
    {
      $params = array_merge(array('rich' => true, 'withtime' => true, 'calendar_button_img' => sfConfig::get('sf_admin_web_dir').'/images/date.png'), $params);
    }
    elseif ($type == CreoleTypes::FLOAT || $type == CreoleTypes::DOUBLE || $type == CreoleTypes::DECIMAL || $type == CreoleTypes::NUMERIC || $type == CreoleTypes::REAL )
    {
      if (!array_key_exists('helper', $params))
      {
        $params = array_merge($params, array('helper' => 'format_number'));
      }
    }

    // user sets a specific tag to use
    if ($inputType = $this->getParameterValue('edit.fields.'.$column->getName().'.type'))
    {
      if ($inputType == 'plain')
      {
        return $this->getColumnListTag($column, $params);
      }
      else
      {
        return $this->getPHPObjectHelper($inputType, $column, $params);
      }
    }

    // guess the best tag to use with column type
    return parent::getCrudColumnEditTag($column, $params);
  }

  /**
   * Returns HTML code for an action option in a select tag.
   *
   * @param string  The action name
   * @param array   The parameters
   *
   * @return string HTML code
   */
  public function getOptionToAction($actionName, $params)
  {
    $options = isset($params['params']) ? sfToolkit::stringToArray($params['params']) : array();

    // default values
    if ($actionName[0] == '_')
    {
      $actionName = substr($actionName, 1);
      if ($actionName == 'deleteSelected')
      {
        $params['name'] = 'Delete Selected';
      }
    }
    $name = isset($params['name']) ? $params['name'] : $actionName;

    $options['value'] = $actionName;

    $phpOptions = var_export($options, true);

    return '[?php echo content_tag(\'option\', __(\''.$name.'\')'.($options ? ', '.$phpOptions : '').') ?]';
  }

  /**
   * Implements helpers for lists
   * Usage: specify helper (and helper_params) in field definition
   */
  public function getColumnListTag($column, $params = array())
  {
    return $this->getColumnHelper(parent::getColumnListTag($column, $params), $column, $params);
  }

  /**
   * Checks and adds helper for column values
   * Usage: specify helper (and helper_params) in field definition
   * @param string $value
   * @param object $column
   * @param array $params
   */
  public function getColumnHelper($value, $column)
  {
    if ($helper = $this->getParameterValue('list.fields.'.$column->getName().'.helper'))
    {
      if ($helperparams = $this->getParameterValue('list.fields.'.$column->getName().'.helper_params'))
      {
        $helperparams = is_array($helperparams) ? $helperparams : sfToolkit::stringToArray($helperparams);
        $valuepairs = '';
        foreach ($helperparams as $parameter=>$value)
          $valuepairs .= '\''.$parameter.'\'=>\''.$value.'\',';
        $helperparams = ', array('.$valuepairs.')';
      }

      return $helper.'('.$value.$helperparams.')';
    }
    else
    {
      return $value;
    }
  }


  /**
   * Get filter attached to column
   * @param object $column
   * @return object $column
   */
  public function getColumnFilterColumn($column)
  {
    // Set filtercolumn default to column name
    $filtercolumn  = $column->getName();

    // Check the existence of a filtermap
    if  ($filtermap  = $this->getParameterValue('list.filter_header_map') and array_key_exists($filtercolumn , $filtermap))
    {
      // Rename the filter to match filter list
      $filtercolumn = $filtermap[$filtercolumn];
    }

    // Check if filter is set for column
    if ($filters = $this->getParameterValue('list.filters') and in_array($filtercolumn, $filters)!=false)
    {
      // Return the connected admin field
      list($field, $flags) = $this->splitFlag($filtercolumn);
      return $this->getAdminColumnForField($field, $flags);
    }
  }

  /**
   * Implements custom filtertypes
   * Usage: filtertype is field definition
   */
  public function getColumnFilterTag($column, $params = array())
  {
    $user_params = $this->getParameterValue('list.fields.'.$column->getName().'.params');
    $user_params = is_array($user_params) ? $user_params : sfToolkit::stringToArray($user_params);
    $params      = $user_params ? array_merge($params, $user_params) : $params;

    // Check for / in field name
    if (strpos($column->getName(),'::')!==false)
    {
      //die($column->getName());
    }

    // user sets a specific tag to use
    if ($inputType = $this->getParameterValue('list.fields.'.$column->getName().'.filtertype') and
      !$column->isComponent() and
      !$column->isPartial()
    )
    {
      $default_value = "isset(\$filters['".$column->getName()."']) ? \$filters['".$column->getName()."'] : null";
      $unquotedName = 'filters['.$column->getName().']';
      $name = "'$unquotedName'";

      $size = ($column->getSize() < 15 ? $column->getSize() : 15);
      $params = $this->getObjectTagParams($params, array('size' => $size));

      return "$inputType($name, $default_value, $params)";
    }
    // many-to-many, indicated by the presence of param through_class
    else if (array_key_exists('through_class', $params))
    {
      $options1 = "_get_options_from_objects(sfPropelManyToMany::getAllObjects(new " . $this->getClassName(). ", '" . $params['through_class'] . "', NULL, '" . ($params['peer_method'] ? $params['peer_method'] : 'doSelect') . "')";
      $options2 = "options_for_select($options1), $default_value, array('include_blank'=>true))";
      $params = "array('style'=>'width:109px;')";
      return "select_tag($name, $options2, $params)";
    }
    else
    {
      return parent::getColumnFilterTag($column, $params);
    }
  }

  /**
   * Build function to retrieve column value
   * @param object column $column
   * @param boolean $developed (optional) writes out function with current class
   * @param string $prefix
   */
  function getColumnGetter($column, $developed = false, $prefix = '')
  {
    if ($developed)
    {
      return $this->processColumnName($column->getPhpName(), $prefix, 'get');
    }
    else
    {
      // Get first part and transform into Method
      $parts = explode('::', $column->getPhpName());
      return 'get'.$parts[0];
    }
  }

  /**
   * Build function to set column value
   * @param object column $column
   * @param boolean $developed (optional) writes out function with current class
   * @param string $prefix
   */
  function getColumnSetter($column, $developed = false, $prefix = '')
  {
    if ($developed)
    {
      return $this->processColumnName($column->getPhpName(), $prefix, 'set');
    }
    else
    {
      // Get first part and transform into Method
      $parts = explode('::', $column->getPhpName());
      return 'set'.$parts[0];
    }
  }

  function getColumnConstant($column)
  {
     // Detect if there is a dot indicating foreign model peer class
    $name = $column->getName();
    if (strpos($name,'/')!==false)
    {
      $nameParts = explode('/', $name);
      $namePartsCount = count($nameParts)-1;
      if ($nameParts[$namePartsCount]=='')
        sfContext::getInstance()->getLogger()->error('Can not use __toString method with '.$name.' In getColumnConstant');

      return $nameParts[$namePartsCount-1].'Peer::'.strtoupper($nameParts[$namePartsCount]);
    }
    else
    {
      return $this->getPeerClassName().'::'.strtoupper($column->getName());
    }
  }

  /**
   * Process the column name to check for foreign model peer classes
   * A / indicates the existence of a foreign model peer, like:
   *   modelpeer/modelpeer/field
   * If fieldname is empty after the /, the __toString method is called.
   * Produces a string like:
   * (($modelvalue=$singularname->getmodelpeer())?(($modelvalue = $modelvalue->getmodelpeer())?$modelvalue->getField():''):'')
   * @param string $phpName column name to process
   * @param string $method (optional) function prefix
   */
  protected function processColumnName($PhpName, $prefix='', $method='get', $iterated = false)
  {
    // Detect if there is / or :: indicating foreign model peer class
    if (strpos($PhpName,'::')!==false)
    {
      list($peer, $PhpName) = explode('::', $PhpName,2);
      $PhpNameExploded = explode('::', $PhpName);

          // _ means use __toString method of model peer class
      if ($PhpName == '')
      {
        $PhpName = '$modelvalue';
      }
      else
      {
        // Reiterate for deeper link
        $PhpName = $this->processColumnName($PhpName, $prefix, $method, true);
      }

      if ($iterated)
      {
        return sprintf('(($modelvalue = $modelvalue->%s%s())?%s:\'\')', $method, strtolower($peer), $PhpName);
      }
      else
      {
        return sprintf('(($modelvalue = $%s->%s%s())?%s:\'\')', $this->getSingularName(), $method, $peer, $PhpName);
      }
    }
    elseif ($iterated)
    {
     return sprintf('$modelvalue->%s%s()', $method, $PhpName);
    }
    else
    {
      return sprintf('$%s%s->%s%s()', $prefix, $this->getSingularName(), $method, $PhpName);
    }
  }

// For debugging purposes only
//  public function getColumns($paramName, $category = 'NONE')
//  {
//    print 'getColumns: '.$paramName.'/'.$category;
//    return parent::getColumns($paramName, $category);
//  }
}