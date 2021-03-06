<?php
/**
 * Adds new functionality regarding Extjs2 to the sfAdminCustomGenerator from DrCore
 * - related fields
 * To use add the following to generate.yml:
 * theme: extjs
 *
 */
class sfExtjsPropelAdminGenerator extends sfAdminCustomGenerator
{
  protected $tableDelimiter,
            $controller,
            $fieldType,
            $fieldList,
            $colArr,
            $columnObjects,
            $displayColumns,
            $editColumns,
            $filterColumns,
            $expandColumns,
            $groupColumns,
            $listDisplayColumnsConfig;

  /**
   * Initializes the current sfGenerator instance.
   *
   * @param sfGeneratorManager A sfGeneratorManager instance
   */
  public function initialize($generatorManager)
  {
    parent::initialize($generatorManager);

    // get the controller, used for URL creation
    $this->controller = sfContext::getInstance()->getController();

    $this->tableDelimiter = sfConfig::get('app_sf_extjs_theme_plugin_table_delimiter', '-');

  }

  /**
   * Creates a partial file if it does not exist
   *
   * @param string The partial filename
   * @param string The contents of the partial file
   *
   * @return null
   */
  public function getCustomPartials($objName,$type='method')
  {
    $partialStr ='';
    $customs =  $this->getParameterValue("$objName.$type");
    if(isset($customs['partials']))
    {
      if (!is_array($customs['partials']))
      {
        $customs['partials'] = array($customs['partials']);
      }

      $partialStr .= "\n// generator $type partials\n";

      foreach($customs['partials'] as $custom)
      {
        $partialStr .= "include_partial('".substr($custom,1)."', array('sfExtjs2Plugin' => \$sfExtjs2Plugin, '$objName' => \$$objName));\n";
        $this->createPartialFile($custom,'<?php // @object $sfExtjs2Plugin and @object $'.$objName.' provided ?>');
      }
    }
    return $partialStr;
  }

  /**
   * Creates a partial file if it does not exist
   *
   * @param string The partial filename
   * @param string The contents of the partial file
   *
   * @return null
   */
  public function createPartialFile($partialName,$contents='',$inModuleDir=true)
  {
    if($inModuleDir)
    {
      $file = sfConfig::get('sf_app_module_dir').DIRECTORY_SEPARATOR.
        $this->getModuleName().DIRECTORY_SEPARATOR.
        sfConfig::get('sf_app_template_dir_name').DIRECTORY_SEPARATOR.
        "$partialName.php";

      if (!file_exists($file))
      {
        if(!file_exists(dirname($file))) mkdir(dirname($file), 0777);
        if(is_writable(dirname($file)))
        {
          file_put_contents($file,$contents);
          chmod($file,0666);
        }
      }
    }

    //put it in the cache folder too in case we can't write to application/modules/templates
    $cacheFile = sfConfig::get('sf_module_cache_dir').DIRECTORY_SEPARATOR.
      'auto'.ucfirst($this->getModuleName()).DIRECTORY_SEPARATOR.
      sfConfig::get('sf_app_template_dir_name').DIRECTORY_SEPARATOR.
      "$partialName.php";

    file_put_contents($cacheFile,$contents);
    chmod($cacheFile,0666);
  }


  public function getClassGetters($objName,$getters)
  {
    $getterStr = "/* %1\$s class getters */\n";
    foreach($getters as $getter)
    {
      $getterStr .= '$gridpanel->attributes["get".ucfirst("'.$getter.'")] = $sfExtjs2Plugin->asMethod("return this.'.$getter.'");'."\n";
    }
    return sprintf($getterStr,$objName);
  }

  public function getStandardPartials($objName,$partialsArr=array('constructor','initComponent','initEvents'),$prefix='list')
  {
    $partialStr = "\n/* %1\$s methods and variables */\n";
    foreach($partialsArr as $partials)
    {
      switch($partials)
      {
        case 'constructor':
          $this->createPartialFile('_'.$prefix.'_'.$objName.'_method_constructor_js',$this->createStandardConstructorPartial($objName),false);
          $partialStr .= "// constructor\n".'include_partial("'.$prefix.'_%1$s_method_constructor_js", array("sfExtjs2Plugin" => $sfExtjs2Plugin, "%1$s" => $%1$s, "className" => $className));'."\n";
          break;
        case 'initComponent':
          $this->createPartialFile('_'.$prefix.'_'.$objName.'_method_initComponent_js',$this->createStandardInitComponentPartial($objName),false);
          $partialStr .= "// initComponent\n".'include_partial("'.$prefix.'_%1$s_method_initComponent_js", array("sfExtjs2Plugin" => $sfExtjs2Plugin, "%1$s" => $%1$s, "className" => $className));'."\n";
          break;
        case 'initEvents':
          $this->createPartialFile('_'.$prefix.'_'.$objName.'_method_initEvents_js',$this->createStandardInitEventsPartial($objName),false);
          $partialStr .= "// initEvents\n".'include_partial("'.$prefix.'_%1$s_method_initEvents_js", array("sfExtjs2Plugin" => $sfExtjs2Plugin, "%1$s" => $%1$s, "className" => $className));'."\n";
          break;
      }
    }
    return sprintf($partialStr,$objName);
  }

  protected function createStandardConstructorPartial($objName)
  {
    return sprintf('<?php
// constructor
$configArr["parameters"] = "c";
$configArr["source"] = "
// %1$s config
this.%1$s_config = ".(isset($%1$s->config_array) ? $sfExtjs2Plugin->asAnonymousClass($%1$s->config_array) : \'{}\').";

// combine %1$s config with arguments
Ext.app.sx.$className.superclass.constructor.call(this, Ext.apply(this.%1$s_config, c));
";
$%1$s->attributes["constructor"] = $sfExtjs2Plugin->asMethod($configArr);',
    $objName);
  }

  protected function createStandardInitComponentPartial($objName)
  {
    return sprintf('<?php
// initComponent
$configArr["source"] = "Ext.app.sx.$className.superclass.initComponent.apply(this, arguments);";
$%1$s->attributes["initComponent"] = $sfExtjs2Plugin->asMethod($configArr);',
    $objName);
  }

  protected function createStandardInitEventsPartial($objName)
  {
    return sprintf('<?php
// initEvents
$configArr["source"] = "Ext.app.sx.$className.superclass.initEvents.apply(this);";
$%1$s->attributes["initEvents"] = $sfExtjs2Plugin->asMethod($configArr);',
    $objName);
  }

  public function getDataStoreConfig()
  {
    $sort_field = $this->getParameterValue('list.sort', null);
    $sort_dir = 'asc';
    if (is_array($sort_field))
    {
      $sort_dir   = $sort_field[1];
      $sort_field = $sort_field[0];
    }

    $group_field = $this->getParameterValue('list.grouping.field', null);

    $displayColumnsConfig = $this->getListDisplayColumnsConfig();
    $groupedColumns = $this->getColumnsGrouped();

    $jsonReader = array(
      'id'            => $groupedColumns['pk']->getName(),
      'root'          => 'data',
      'totalProperty' => 'totalCount',
      'fields'        => $displayColumnsConfig['listDisplay']
    );

    $httpProxy = array(
      'url' => $this->controller->genUrl($this->getModuleName().'/list'),
      'method' => 'POST'
    );

    $options = array();

    //set default sort, can be overruled by groupfield
    if ($sort_field)
    {
      $options['sortInfo'] = array(
        'field'     => str_replace('/', $this->tableDelimiter, $sort_field),
        'direction' => $sort_dir
      );
    }
    if ($group_field)
    {
      $options['groupField'] = ($this->getParameterValue('list.grouping.start_grouped', true)) ? $group_field : null;
      $options['remoteGroup'] = 'true';
      $options['sortInfo'] = array(
        'field' => $group_field,
        'direction' => 'asc'
      );
    }
    $options['remoteSort'] = 'true';
    $options['baseParams'] = array(
      'json' => true
    );

    return array('options' => $options, 'reader' => $jsonReader, 'proxy' => $httpProxy, 'cred_arr' =>$displayColumnsConfig['credArr']);
  }

  public function getColumnModelConfig()
  {
    $plugins = false;
    $cmItems = array();
    $credArr = array();
    $i=0;
    foreach ($this->sortColumns($this->getListColumns($this->getColumnsGrouped())) as $column)
    {
      //don't create column config for invisible columns
      if (($column->isInvisible()))
      {
        continue;
      }

      //captures the ^expander in the list.display and sets the rowexpander at that position
      //new syntax is ^expander, need to obsolete plain *
      if ($column->key == '*'||$column->key == '^expander')
      {
        $cmItems[] = "{xtype: 'rowexpander'}";
        $i++;
        continue;
      }

      //handle credentials for displaying the column
      $listcreds = $this->getParameterValue('list.fields.'.$column->key.'.credentials');
      if ($listcreds)
      {
        $listcreds = str_replace("\n", ' ', var_export($listcreds, true));
        //if the user doesn't have the right permissions remove the columnconfig
        $credArr[] = 'if(!$sf_user->hasCredential('.$listcreds.')) unset($columnmodel->config_array['.$i.']);';
      }

      $columnDefinition = $this->getColumnAjaxListDefinition($column, $this->getColumnsGrouped());

      //captures the ^rowactions in the list.display and sets the rowexpander at that position
      if ($column->key == '^rowactions')
      {
        //handle credentials for displaying the rowaction column
        $listRowactions = $this->getParameterValue('list.rowactions');
        foreach ((array) $listRowactions as $actionName => $params)
        {
          $actioncreds = (isset($params['credentials']))?$params['credentials']:false;
          if ($actioncreds)
          {
            $actioncreds = str_replace("\n", ' ', var_export($actioncreds, true));
            //if the user doesn't have the right permissions remove the button config
            $credArr[] = 'if(!$sf_user->hasCredential('.$actioncreds.')) unset($columnmodel->config_array['.$i.']);';
          }
        }

        $pluginArrName = strtolower($this->getModuleName()).'_rowactions';
        //here are the defaults if nothing is set in the fields config
        $plugins[$pluginArrName] = array('xtype' => 'list'.strtolower($this->getModuleName()).'rowactions', 'header' => '&nbsp;');
        //merge in the fields config if it's there
        if($this->getParameterValue('fields.'.$column->key,false)||$this->getParameterValue('list.fields.'.$column->key,false)){
          $plugins[$pluginArrName] = array_merge($plugins[$pluginArrName], $columnDefinition);
        }
        //set the column item to our generated plugin
        $cmItems[] = 'this.'.$pluginArrName;
        $i++;
        continue;
      }

      //handle edit credentials for plugin columns
      if($this->getParameterValue('list.fields.'.$column->key.'.plugin'))
      {
        $pluginArrName = $column->key.'_'.$this->getParameterValue('list.fields.'.$column->key.'.plugin');
        //setup the data for generating the new plugin instance
        $plugins[$pluginArrName] = $columnDefinition;
        if ($editcreds = $this->getParameterValue('edit.fields.'.$column->key.'.credentials'))
        {
          $editcreds = str_replace("\n", ' ', var_export($editcreds, true));
          //pass our credentials down to the generated partial
          $credArr[] = "if(!\$sf_user->hasCredential($editcreds)&& is_array(\$columnmodel->plugins['$pluginArrName']))\$columnmodel->plugins['$pluginArrName']['editable'] = false;";
        }
        //set the column item to our generated plugin
        $cmItems[] = 'this.'.$pluginArrName;
        $i++;
        continue;
      }

      //handle edit credentials for non-plugin columns
      if ($editcreds = $this->getParameterValue('edit.fields.'.$column->key.'.credentials'))
      {
        $editcreds = str_replace("\n", ' ', var_export($editcreds, true));
        //unset the editor if the user doesn't have the right credentials
        $credArr[] = "if(!\$sf_user->hasCredential($editcreds)&& is_array(\$columnmodel->config_array['$i'])&& isset(\$columnmodel->config_array['$i']['editor'])) unset(\$columnmodel->config_array['$i']['editor']);
if(!\$sf_user->hasCredential($editcreds)&& is_array(\$columnmodel->config_array['$i'])) \$columnmodel->config_array['$i']['editable'] = false;";
      }

      $cmItems[] = $columnDefinition;

      $i++;
    }
    return array('plugins'=>$plugins, 'cred_arr' =>$credArr, 'col_items'=>$cmItems);
  }

  public function getToolbarTopConfig()
  {
    $listActions = $this->getParameterValue('list.actions');
    if (null === $listActions)
    {
      $listActions = array(
        '_create' => array(),
        '_refresh' => array(),
      );
    }

    $configArr = array();
    $credArr = array();
    $i=0;
    foreach ((array) $listActions as $actionName => $params)
    {
      $buttoncreds = (isset($params['credentials']))?$params['credentials']:false;
      if ($buttoncreds)
      {
        $buttoncreds = str_replace("\n", ' ', var_export($buttoncreds, true));
        //if the user doesn't have the right permissions remove the button config
        $credArr[] = 'if(!$sf_user->hasCredential('.$buttoncreds.')) unset($toolbar_top->config_array[\'items\']['.$i.']);';
      }
      $configArr[] = $this->getAjaxButtonToToolbarAction($actionName, $params, false);
      $i++;
    }
    return array('actions_config'=>$configArr,'cred_arr'=>$credArr, 'list_actions'=>$listActions);
  }

  public function getFilterPanelConfig()
  {
    // iterate through all (related) columns of all classes
    $groupedColumns = $this->getColumnsGrouped('list.filters');
    $columns = $this->getListColumns($groupedColumns);
    $tableName = $this->getTableName();

    $temp = $formFields = array();
    $credArr = array();
    foreach ($columns as $column)
    {
      $temp[$column->index] = $column;
    }
    // do real sorting
    ksort($temp);
    // put sorted array back
    $columns  = $temp;

    $i = 0;
    foreach ($columns as $column)
    {
      $type = $column->getCreoleType();
      $columnName = $column->key;
      $credentials = $this->getParameterValue('list.fields.'.$columnName.'.credentials');
      if ($credentials)
      {
        $credentials = str_replace("\n", ' ', var_export($credentials, true));
        $credArr[] = 'if(!$sf_user->hasCredential('.$credentials.')) unset($filterpanel->config_array["items"]['.$i.']);';
      }

      $fieldArr = $this->getColumnAjaxFilterDefinition($column, $groupedColumns);

      if(isset($fieldArr['renderer'])) unset($fieldArr['renderer']);
      $formFields[] = $fieldArr;

      //TODO, change this so drop-down columnboxes and checkboxes appear...
      //$formFields[] = array('fieldLabel' => str_replace("'", "\\'", $this->getParameterValue('list.fields.'.$columnName.'.name')), 'name' => 'filters['.str_replace('/', $this->tableDelimiter, $columnName).']');
      $i++;
    }
    return array('filter_config'=>$formFields,'cred_arr'=>$credArr);
  }

  public function getGridPanelConfig()
  {
    if($this->getParameterValue('list.grouping.text_tpl',false))
    {
      $grid_view['groupTextTpl'] = $this->getParameterValue('list.grouping.text_tpl');
    }

    $pluginArr = false;
    $listPlugins = $this->getParameterValue('list.plugins',false);
    if($listPlugins)
    {
      $pluginArr = (!is_array($listPlugins)) ? array($listPlugins) : $listPlugins;
    }

    $listDisplay = $this->getParameterValue('list.display', false);
    if ($listDisplay)
    {
      foreach($listDisplay as $col)
      {
        if($this->getParameterValue('list.fields.'.$col.'.plugin'))
        {
          $pluginArr[] = 'this.cm.'.$col.'_'.$this->getParameterValue('list.fields.'.$col.'.plugin');
        }
        if($col == '^rowactions')
        {
          $pluginArr[] = 'this.cm.'.strtolower($this->getModuleName()).'_rowactions';
        }
      }
    }

    $expander =  $this->getParameterValue('list.expand_columns');
    $expanderPartial = '';
    if (isset($expander['renderer_partial']))
    {
      $pluginArr[] = 'this.rowExpander';

      if (!is_array($expander['renderer_partial']))
      {
        $expander['renderer_partial'] = array($expander['renderer_partial']);
      }

      if (isset($expander['fields']))
      {
        if (!is_array($expander['fields']))
        {
          $expander['fields'] = array($expander['fields']);
        }
        $template = '';
        foreach($expander['fields'] as $field)
        {
          $template .= "<tr><td><p>{".str_replace('/','-',$field)."}</p></td></tr>";
        }
      }

      $expanderPartial .= "\n// generator expand columns renderer partial\n";
      foreach($expander['renderer_partial'] as $expanderRenderer)
      {
        $this->createPartialFile($expanderRenderer,'<?php // @object $sfExtjs2Plugin and @object $gridpanel provided
    $configArr["source"] = "
    if(typeof this.rowExpander ==\'undefined\')
    {
      this.rowExpander = Ext.ComponentMgr.create({
        xtype: \'rowexpander\',
        tpl : new Ext.Template(
          \'<table width=\"100%\">'.$template.'</table>\'
        )
      });
    }
    return this.rowExpander";
    $gridpanel->attributes["getRowExpander"] = $sfExtjs2Plugin->asMethod($configArr);
  ?>');

        $expanderPartial .= "include_partial('".substr($expanderRenderer,1)."', array('sfExtjs2Plugin' => \$sfExtjs2Plugin, 'gridpanel' => \$gridpanel));\n";
      }
    }

    $user_params = $this->getParameterValue('gridpanel.params', array());
    if (isset($user_params['bbar'])) unset($user_params['bbar']);
    if (isset($user_params['tbar'])) unset($user_params['tbar']);

    return array('expander_partial'=>$expanderPartial,'user_params'=>$user_params,'plugin_arr'=>$pluginArr);
  }

  public function getRowActionsConfig()
  {
    $listRowactions = $this->getParameterValue('list.rowactions');

    $configArr = array();
    $credArr = array();
    $i=0;
    foreach ((array) $listRowactions as $actionName => $params)
    {
    //handle credentials for displaying the action
      $actioncreds = (isset($params['credentials']))?$params['credentials']:false;
      if ($actioncreds)
      {
        $actioncreds = str_replace("\n", ' ', var_export($actioncreds, true));
        //if the user doesn't have the right permissions remove the button config
        $credArr[] = 'if(!$sf_user->hasCredential('.$actioncreds.')) unset($rowactions->config_array[\'actions\']['.$i.']);';
      }
      $configArr[] = $this->getAjaxRowAction($actionName, $params);
      $i++;
    }
    return array('actions_config'=>$configArr,'cred_arr'=>$credArr);
  }

  /**
   * Configures the display columns config used in several modules
   *
   * @return array($listDisplay, $credArr)
   *
   */
  public function getListDisplayColumnsConfig()
  {
    if(!$this->listDisplayColumnsConfig)
    {
      $i=0;
      $this->listDisplayColumnsConfig['credArr'] = array();
      $this->listDisplayColumnsConfig['listDisplay'] = array();
      $uniqueCols = $this->getListUniqueColumns($this->getColumnsGrouped(), true);
      foreach ($uniqueCols as $column)
      {
        if ($column->isPlugin()) continue;  //plugin placeholder columns not in json-data
        if ($column->isPartial()) continue; //partials will not end up in json-data

        $columnName = $column->key;
        $fieldName = str_replace('/', $this->tableDelimiter, $columnName);

        $credentials = $this->getParameterValue('list.fields.'.$columnName.'.credentials');
        if ($credentials)
        {
          $credentials = str_replace("\n", ' ', var_export($credentials, true));
          $this->listDisplayColumnsConfig['credArr'][] = 'if(!$sf_user->hasCredential('.$credentials.')) unset($reader["fields"]['.$i.']);';
        }

        $this->listDisplayColumnsConfig['listDisplay'][] = array(
         'name' => $fieldName,
         'type' => $this->getFieldTypeForReader($column)
        );
        $i++;
      }
    }
    return $this->listDisplayColumnsConfig;
  }

  public function getConfigColumns($displays=array('display'))
  {
    $this->colArr = array('list.display','list.edit','list.filters','list.expand_columns.fields','list.grouping.field');
    if(!is_array($displays))
    {
      $displays = array($displays);
    }

    $columnsArr = array();
    foreach($displays as $display)
    {
      $colArr = array();
      switch($display)
      {
        case 'display':
          if(!$this->displayColumns)
          {
            $this->displayColumns = $this->getColumnsForDisplay($this->colArr[0]);
          }
          $colArr = $this->displayColumns;
          break;

        case 'edit':
          if(!$this->editColumns)
          {
            $this->editColumns = $this->getColumnsForDisplay($this->colArr[1]);
          }
          $colArr = $this->editColumns;
          break;
        case 'filters':
          if(!$this->filterColumns)
          {
            $this->filterColumns = $this->getColumnsForDisplay($this->colArr[2]);
          }
          $colArr = $this->filterColumns;
          break;
        case 'expand':
          if(!$this->expandColumns)
          {
            $this->expandColumns = $this->getColumnsForDisplay($this->colArr[3]);
          }
          $colArr = $this->expandColumns;
          break;
        case 'group':
          if(!$this->groupColumns)
          {
            $this->groupColumns = $this->getColumnsForDisplay($this->colArr[4]);
          }
          $colArr = $this->groupColumns;
          break;
      }
      $columnsArr = array_merge($columnsArr,$colArr);
    }
    return $columnsArr;
  }

  protected function getColumnsForDisplay($display)
  {
    $columns = $this->getColumnObjects();
    $columnArr = array('pk'=> $columns['pk'], 'columns' => array(), 'related' => array());

    foreach($columns['columns']['NONE'] as $column)
    {
      if($column->displayArr && in_array($display,$column->displayArr))
      {
        $columnArr['columns']['NONE'][] = $column;
      }
    }

    foreach($columns['related'] as $key => $col)
    {
      $columnArr['related'][$key] = array('pk' => $col['pk'], 'columns' => array(), 'related'=> $col['related']);
      foreach($col['columns']['NONE'] as $column)
      {
        if($column->displayArr && in_array($display,$column->displayArr))
        {
          $columnArr['related'][$key]['columns']['NONE'][] = $column;
        }
      }
    }
    return $columnArr;
    //return $this->getColumnObjects();
  }

  /**
   * Gets all fields defined in the generator.yml
   *
   * @return array array(fieldName => array(identifying_parameter))
   */
  public function getFieldList()
  {
    if(!$this->fieldList)
    {
      $this->columnList = array();


      foreach ($this->colArr as $param)
      {
        $fields = $this->getParameterValue($param, array());

        // if no fields are defined in generator.yml file, get all default fields
        if ($param == 'list.display' && count($fields) == 0)
        {
          foreach ($this->getTableMap()->getColumns() as $column)
          {
            $fields[] = sfInflector::underscore($column->getPhpName());
          }
        }
        if (!$fields) continue;

        if (!is_array($fields))
        {
          //$fields = array('NONE' => $fields);
          //$this->fieldList[$fields][] = $param;
          //continue;
          $fields = array($fields);
        }

        // categories?
        if (isset($fields[0]))
        {
          // simulate a default one
          $fields = array('NONE' => $fields);
        }

        // add group-name to field-name
        $fieldsWithGroup = array();
        foreach ($fields as $group => $fieldNames)
        {
          foreach ($fieldNames as $fieldName)
          {
            $fieldsWithGroup[] = $group.'\\'.$fieldName;
          }
        }

        foreach($fieldsWithGroup as $field)
        {
          $this->fieldList[$field][] = $param;
        }

      }
    }
    return $this->fieldList;
  }

  /**
   * Wraps a content for I18N.
   *
   * @param string The key name
   * @param string The defaul value
   *
   * @return string HTML code
   */
  public function getI18NString($key, $default = null, $withEcho = true)
  {
    $value = $this->escapeString($this->getParameterValue($key, $default));

    // find %%xx%% strings
    preg_match_all('/%%([^%]+)%%/', $value, $matches, PREG_PATTERN_ORDER);
    $fields = array();
    foreach ($matches[1] as $name)
    {
      $fields[] = $name;
    }

    $i = 0;
    $vars = array();

    foreach ($fields as $field)
    {
      $column = $this->getAdminColumnForField($field);
      $column->key = $field;

      if ($column->isLink())
      {
        $vars[] = '\'%%'.$matches[1][$i].'%%\' => link_to('.$this->getColumnListTag($column).', \''.$this->getModuleName().'/edit?'.$this->getPrimaryKeyUrlParams().')';
      }
      elseif ($column->isPartial())
      {
        $vars[] = '\'%%_'.$matches[1][$i].'%%\' => '.$this->getColumnListTag($column);
      }
      else if ($column->isComponent())
      {
        $vars[] = '\'%%~'.$matches[1][$i].'%%\' => '.$this->getColumnListTag($column);
      }
      else
      {
        $vars[] = '\'%%'.$matches[1][$i].'%%\' => '.$this->getColumnListTag($column);
      }
      $i++;
    }

    // strip all = signs
    $value = preg_replace('/%%=([^%]+)%%/', '%%$1%%', $value);

    $i18n = '__(\''.$value.'\', '."\n".'array('.implode(",\n", $vars).'))';

    return $withEcho ? '[?php echo '.$i18n.' ?]' : $i18n;
  }

  function getAjaxRowAction($actionName, $params)
  {
    $params   = (array) $params;
    $options  = isset($params['params']) ? sfToolkit::stringToArray($params['params']) : array();
    sfLoader::loadHelpers('Partial');
    $default_callback = 'this.'.$actionName;
    $callback = false;
    $default_icon = 'page_white';
    $default_qtip = $actionName;
    $default_hidden = false;
    // default values
    if ($actionName[0] == '_')
    {
      $actionName     = substr($actionName, 1);
      $default_name   = ucfirst(strtr($actionName, '_', ' '));
      $default_action = $actionName;
      switch ($actionName)
      {
        case 'delete':
          $default_icon = 'cross';
          $default_qtip = ucfirst($actionName);
          break;

        case 'progress':
          $default_icon = 'loading.gif';
          $default_qtip = 'Working';
          $default_hidden = true;
          break;
      }
    }
    $icon   = isset($params['icon']) ? sfToolkit::replaceConstants($params['icon']) : $default_icon;
    $qtip = isset($params['qtip']) ? $params['qtip'] : $default_qtip;
    $callback = (!$callback)?"\$sfExtjs2Plugin->asVar('".$default_callback."')":'$sfExtjs2Plugin->asMethod("'.$callback.'")';
    $callback = isset($params['callback']) ? '$sfExtjs2Plugin->asMethod("'.$params['callback'].'")' : $callback;
    $hidden = ($default_hidden) ? 'true' : 'false';
    $jsOptions = "
      'qtip' => '$qtip',
      'iconCls'    => \$sfExtjs2Plugin->asVar(\"Ext.ux.IconMgr.getIcon('".$icon."')\"),
      'cb' => $callback,
      'hide' => $hidden
    ";

    return $jsOptions;

  }
  /**
   * Returns javascript code for an action button in the toolbar new (config)style (with sfExtjs2Plugin usage).
   *
   * @param string  The action name
   * @param array   The parameters
   * @param boolean Whether to add a primary key link or not
   *
   * @return string javascript code
   */
  function getAjaxButtonToToolbarAction($actionName, $params, $pk_link = false)
  {
    $params   = (array) $params;
    $options  = isset($params['params']) ? sfToolkit::stringToArray($params['params']) : array();

    sfLoader::loadHelpers('Partial');

    //general default values
    $default_handler_function = 'this.'.$actionName;
    $handler_function = false;
    $default_icon = 'page_white';

    // default values
    if ($actionName[0] == '_')
    {
      $actionName     = substr($actionName, 1);
      $default_name   = ucfirst(strtr($actionName, '_', ' '));
      $default_action = $actionName;

      switch ($actionName)
      {
        case 'text':
          $text   = isset($params['name']) ? $params['name'] : $default_name;
          return "'xtype'      => 'tb".$actionName."', 'text' => '".$text."'";
        case 'separator':
        case 'spacer':
        case 'fill':
          return "'xtype'      => 'tb".$actionName."'";
        case 'delete':
          $default_icon = 'page_white_delete';
          break;
        case 'create':
          $default_icon = 'page_white_add';
          $default_name = isset($params['name']) ? $params['name'] : 'Add '.$this->getParameterValue('object_name', $this->getModuleName());
          break;
        case 'refresh':
          $default_icon = 'table_refresh';
          break;
        case 'print':
          $default_icon = 'printer';
          break;
        case 'pdf':
          $default_icon = 'page_white_acrobat';
          break;
        case 'upload':
          $default_icon = 'page_white_get';
          break;
        case 'insert':
          $default_icon = 'page_white_add';
          break;
      }
    }
    else
    {
      $default_name   = strtr($actionName, '_', ' ');
      $default_action = 'List'.sfInflector::camelize($actionName);

      // set name up here...
      $name   = isset($params['name']) ? $params['name'] : $default_name;

// OBSOLETE???
//      $handler_url   = isset($params['handler_url']) ? $params['handler_url'] : '';
      $handler_url   = '';

      if ($gridListCreateLink = sfConfig::get('app_sf_extjs_theme_plugin_list_action_handler', null))
      $handler_function = $gridListCreateLink."('$handler_url', '".$default_name."', '".$name."');";
    }

    $name   = isset($params['name']) ? $params['name'] : $default_name;
    $icon   = isset($params['icon']) ? sfToolkit::replaceConstants($params['icon']) : $default_icon;
    $action = isset($params['action']) ? $params['action'] : $default_action;
    $url_params = $pk_link ? '?'.$this->getPrimaryKeyUrlParams() : '\'';
    $handler_function = (!$handler_function)?"\$sfExtjs2Plugin->asVar('".$default_handler_function."')":'$sfExtjs2Plugin->asMethod("'.$handler_function.'")';
    $handler_function = isset($params['handler_function']) ? '$sfExtjs2Plugin->asMethod("'.$params['handler_function'].'")' : $handler_function;

    $jsOptions = "
                  'xtype'      => 'tbbutton', //TODO add option for MenuButtons and possibly others
                  'text'       => '".$name."',
                  'action'     => '".$action."',
                  'idRequired' => false,
                  'iconCls'    => \$sfExtjs2Plugin->asVar(\"Ext.ux.IconMgr.getIcon('".$icon."')\"),
                  'disabled'   => false,
                  'scope'      => \$sfExtjs2Plugin->asVar(\"this\"),
                  'store'      => 'c.store',
                  'handler'    => $handler_function
                 ";

    return $jsOptions;
  }

  /**
   * Returns javascript config-code for an action button in the edit-form.
   *
   * @param string  The action name
   * @param array   The parameters
   * @param boolean Whether to add a primary key link or not
   *
   * @return string javascript config-code
   */
  function getEditAjaxActionToButton($actionName, $params, $edit_ns, $pk_link = false )
  {
    $params   = (array) $params;
    $options  = isset($params['params']) ? sfToolkit::stringToArray($params['params']) : array();

    //general default values
    $default_handler_function = "function(){Ext.Msg.alert('Error','handler_function is not defined!<br><br>define the \'handler_function\' in your generator.yml file.');}";
    $default_hide_when_new = false;
    $default_icon = 'page_white';

    // default values
    if ($actionName[0] == '_')
    {
      $actionName     = substr($actionName, 1);
      $default_name   = ucfirst(strtr($actionName, '_', ' '));
      //$default_icon   = sfConfig::get('sf_admin_web_dir').'/images/'.$actionName.'_icon.png';
      $default_action = $actionName;
      //$default_class  = 'btn_'.$actionName;

      $list_ns = ucfirst(sfInflector::camelize($this->getModuleName()))."List";

      switch ($actionName)
      {
        case 'cancel':
          $default_icon = 'decline';
          $default_handler_function = "function() { this.ownerCt.remove(this); this.fireEvent('close_request', this); }";
          break;

        case 'reload':
          $default_icon = 'page_white_refresh_arrows';
          $default_handler_function = "function() {
              if (!this.getForm().isDirty()) {
                this.loadItem();
              } else {
                Ext.Msg.show({
                  title:'Discard changes?',
                  msg: 'If you reload, your changes will be lost!<br>Are you sure you want to reload?',
                  buttons: Ext.Msg.YESNO,
                  fn: function(btn){
                    if (btn == 'yes') this.loadItem();
                  },
                  scope: this,
                  icon: Ext.MessageBox.WARNING
                });
              }
            }";
          $default_hide_when_new = true;
          break;

        case 'save':
          $default_icon = 'page_white_accept';
          $type = 'submit';

          $default_handler_function = "function() { this.doSubmit() }";
          break;

        case 'savenew':
          $default_icon = 'page_white_add';
          $default_name = 'Save as New';
          $type = 'submit';
          $default_handler_function = "function() { this.doSubmit(true) }";
          $default_hide_when_new = true;
          break;

        case 'delete':
          $default_icon = 'page_white_delete';
          $default_handler_function = "function() { this.deleteItem(); }";
          $default_hide_when_new = true;
          break;

        case 'print':
          $default_icon = 'printer';
          //          $list_ns = ucfirst(sfInflector::camelize($this->getModuleName()))."List";
          $default_handler_function = "function(){window.open('".$this->controller->genUrl($this->getModuleName().'/listPrint')."');}";
          break;
        case 'pdf':
          $default_icon = 'page_white_acrobat';
          //          $list_ns = ucfirst(sfInflector::camelize($this->getModuleName()))."List";
          //          $default_handler_function = "function(){".$list_ns.".getDataStore().reload();}";
          break;
      }
    }
    else
    {
      $default_name   = strtr($actionName, '_', ' '); //TODO: Convert _ to spaces??? for a name...
      $default_action = 'List'.sfInflector::camelize($actionName);
      // set name up here...
      $name   = isset($params['name']) ? $params['name'] : $default_name;
      $handler_url   = isset($params['handler_url']) ? $params['handler_url'] : '';

      if ($gridListCreateLink = sfConfig::get('app_sf_extjs_theme_plugin_list_action_handler', null))
      $default_handler_function = "function(){".$gridListCreateLink."('$handler_url', '".$default_name."', '".$name."');}";
    }

    $name   = isset($params['name']) ? $params['name'] : $default_name;
    $icon   = isset($params['icon']) ? sfToolkit::replaceConstants($params['icon']) : $default_icon;
    $action = isset($params['action']) ? $params['action'] : $default_action;
    $url_params = $pk_link ? '?'.$this->getPrimaryKeyUrlParams() : '\'';
    $handler_function = isset($params['handler_function']) ? $params['handler_function'] : $default_handler_function;

    $jsOptions = array(
    'text' => $name,
    'iconCls'    => 'Ext.ux.IconMgr.getIcon("'.$icon.'")',
    'scope' => 'this',
    'handler' => $handler_function,
    'hide_when_new' => $default_hide_when_new,
    );
    if (isset($type)) $jsOptions['type'] = $type;

    return $jsOptions;
  }

  /**
   * Returns javascript code for an action button in the context menu.
   *
   * @param string  The action name
   * @param array   The parameters
   * @param boolean Whether to add a primary key link or not
   *
   * @return string javascript code
   */
  function getAjaxLinkToAction($actionName, $params)
  {
    // get first primary key of class
    $pkn = $this->getPrimaryKeyAdminColumn()->getName();
    $options = isset($params['params']) ? sfToolkit::stringToArray($params['params']) : array();
    $default_icon = 'page_white';

    // default values
    if ($actionName[0] == '_')
    {
      $actionName = substr($actionName, 1);
      $name       = $actionName;
      $action     = $actionName;

      switch ($actionName)
      {
        case 'edit':
          $default_icon = 'page_white_edit';
          $handler = $list_ns.".addTab('Edit number ' + gridContextMenu.rowRecord['".$pkn."'], '".$this->controller->genUrl($this->getModuleName().'/edit')."', 'edit_' + gridContextMenu.rowRecord['".$pkn."'])";
          break;

        case 'delete':
          $default_icon = 'page_white_delete';
          $handler = "Ext.Msg.alert('Error', 'delete is not implemented');"; // TODO: set handler for delete
          break;
      }
    }
    else
    {
      $name    = isset($params['name']) ? $params['name'] : $actionName;
      $action  = isset($params['action']) ? $params['action'] : 'List'.sfInflector::camelize($actionName);
      $handler = isset($params['handler']) ? $params['handler'] : "window.location.href = '".$this->controller->genUrl($this->getModuleName().'/'.$action)."?".$pkn."=' + gridContextMenu.rowRecord['".$pkn."'];";
    }

    $icon    = isset($params['icon']) ? sfToolkit::replaceConstants($params['icon']) : $default_icon;

    $html = "gridContextMenu.add({ id: 'cm_btn_".$actionName."', text: '[?php echo ucfirst(__('".$name."')) ?]', action: '', handler: handler".ucfirst($actionName).", iconCls: Ext.ux.IconMgr.getIcon('".$icon."')});\n\n";

    $html .= "function handler".ucfirst($actionName)."(item, e) {\n";
    $html .= "\t".$handler."\n";
    $html .= "}\n";

    //return '<li>[?php echo link_to(image_tag(\''.$icon.'\', array(\'alt\' => __(\''.$name.'\'), \'title\' => __(\''.$name.'\'))), \''.$this->getModuleName().$this->tableDelimiter.$action.$url_params.($options ? ', '.$phpOptions : '').') ?]</li>'."\n";

    return $html;
  }

  public function getPrimaryKeyAdminColumn()
  {
    $pks = $this->getPrimaryKey();
    $pk = $pks[0]->getPhpName();

    return new sfExtjsAdminColumn($pk, $pks[0], null);
  }

  public function getColumnObjects()
  {
    if(!$this->columnObjects)
    {
      $this->columnObjects = $this->setupGroupedColumns();
    }
    return $this->columnObjects;
  }

  /**
   * Protected method to recursively setup the groupedColumn-Array
   *
   * @param string[] $columnNames (Array of columnNames which should be added to the columnsGrouped)
   * @param string $peerName
   * @param array() $groupedColumns (Array containing columns in a hierarchy)
   * @param int field start index (used for recursion, to keep track of field-order)
   * @return unknown
   */
  protected function setupGroupedColumns($columnNames=null, $peerName = null, $groupedColumns = array('pk'=> null, 'columns' => array(), 'related' => array()), $i = 0)
  {
    //the array_keys gives a list of all columns specified anywhere in the generator.yml
    //the value of each key is an array of the parameters that defined it
    //$fieldsArr = $this->getFieldList();
    //if(!$columnNames) $columnNames = array_keys($this->getFieldList());

    // the base peerName will be this->PeerName
    if ($peerName == null)
    {
      $peerName = $this->getPeerClassName();
    }

    // if there is only one columnName, place it in an array.
    if (!is_array($columnNames))
    {
      $columnNames = array($columnNames);
    }

    //check if primary key has been defined, else set it
    //WE DON'T HANDLE MULTIPLE PRIMARY KEYS!
    if (!isset($groupedColumns['pk']) || ($groupedColumns['pk'] == array()) || ($groupedColumns['pk'] == null))
    {
      $tableMap = @call_user_func(array($peerName, 'getTableMap'));

      foreach ($tableMap->getColumns() as $col)
      {
        if ($col->isPrimaryKey())
        {
          $column = new sfExtjsAdminColumn($col->getPhpName(), $col, null);
          $groupedColumns['pk'] = $column;
          break; // end foreach when found
        }
      }
    }

    //iterate through all provided columnNames
    foreach ($columnNames as $columnName)
    {
      // get its group (for fieldsets)
      $group = 'NONE';
      if (false !== strpos($columnName, '\\'))
      {
        list($group, $columnName) = explode('\\', $columnName, 2);
      }

      //get flags (should make no difference if you placed flags in front, or in between (for foreign-fields)
      list($columnName, $flags) = $this->splitFlag($columnName);

      //rebuild flags, so they can be propogated to end-field
      $flagPrefix = '';
      foreach ($flags as $flag)
      {
        $flagPrefix .= $flag;
      }

      // if column is foreign
      if (false !== strpos($columnName, '/'))
      {
        list($foreignKey,   $relatedColumnName) = explode('/', $columnName, 2);

        // Add invisible foreign-key
        $fkColumn = $this->getAdminColumnForField($foreignKey, array(), $peerName); //flags are useless, it a foreign-key!

        // get relatedPeerName for propagation
        $relatedTableName = $fkColumn->getColumn()->getRelatedTableName();
        $relatedTable = $this->getMap()->getDatabaseMap()->getTable($relatedTableName);
        $relatedPeerName = $relatedTable->getPhpName().'Peer';

        //add an option to show this field should be visible
        $fkColumn->visible = false;
        $fkColumn->key = $foreignKey;
        $fkColumn->index = $i; //foreign key have the same index!
        //$fkColumn->displayArr = $fieldsArr[implode('\\',array($group,$flagPrefix.$columnName))];
        $groupedColumns['columns'][$group][] = $fkColumn; // Add foreign-key-Column to columns //TODO: maybe add them under their own key (fks or something, which would also remove the need for the property (in)visible)

        // check if related groupedColumn hierarchy is already defined, if not define it.
        $related = array('pk' => null, 'columns' => array(), 'related' => array());
        if (isset($groupedColumns['related'][$foreignKey]))
        {
          $related = $groupedColumns['related'][$foreignKey];
        }

        /*
         * add foreign column recursively (so it can for instance handle productgroup/product/name and beyond)
         *
         * provide:
         *  - stripped fieldname (propogated group and flags)
         *  - relative peername
         *  - stepped in hierarchy (related)
         *  - increased counter for display-order
         */
        $groupedColumns['related'][$foreignKey] = $this->setupGroupedColumns($group.'\\'.$flagPrefix.$relatedColumnName, $relatedPeerName, $related, $i++);  //$relatedTableName

      }
      // local columns (which should be visible (so E.G. list.display, edit.display or list.filters or [list.display and list.group.display]))
      else
      {
        // this gets/creates the local column to be stored in the hierarchy (containing info like flags, PhpName, and the ColumnMap
        $column = $this->getAdminColumnForField($columnName, $flags, $peerName);

        // add an option to show this field should be visible
        $column->visible = true;
        //set the key, used to be able to retreive its relative location from within the hierarchy, when retreiving (based on from where it is retreived, with recursion)
        //        $columnUnderscore = sfInflector::underscore($column->getPhpName());
        $column->key = $columnName; //$columnUnderscore; // this should probably be the same as $columnName
        //set the display-sorting-index
        $column->index = $i++;
        // columns can be added multiple times to the hierarchy
        // filtering double fields should be done elsewhere (probably during json-encoding and columnmodel creation) or
        // else you cannot see a column multiple times in your grid (if someone happened to want that)
        //$column->displayArr = (isset($fieldsArr[implode('\\',array($group,$flagPrefix.$columnName))]))?$fieldsArr[implode('\\',array($group,$flagPrefix.$columnName))]:null;
        $groupedColumns['columns'][$group][] = $column;
      }

    } // process next column

    return $groupedColumns;
  }

  /**
   * Return the columns and all related columns with their primary key grouped for a specific view.
   *
   * @param string $for      (which can be e.g. 'list.display', 'edit.display' or 'filters.filters' or multiple by providing an array: array('list.display', 'edit.display'))
   * @param boolean $strict  disables automatic additions when set to true (like adding list.grouping.field and list.grouping.display, when asking for list.display)
   * @return                 hierarchically arranged (recursively per related table) array of columns
   */
  public function getColumnsGrouped($for = array('list.display'), $strict = false)
  {
    if (!is_array($for))
    {
      $for = array($for);
    }

    $columnNames = array();

    if (!$strict)
    {
      // check if you want to display list, add groupes automatically
      if (in_array('list.display', $for))
      {
        // add our expand column to the datastore
        if($this->getParameterValue('list.expand_columns.fields'))
        {
          $for[] = 'list.expand_columns.fields';
        }
      }
      // check if you want to display edit, add pages automatically
      if (in_array('edit.display', $for))
      {
        $pages_level = array('edit.pages');

        while (count($pages_level) > 0)
        {
          $pages = $this->getParameterValue($pages_level[0], array());

          foreach ($pages as $pageName => $page)
          {
            $for[] = $pages_level[0].'.'.$pageName.'.display';

            //add to the bottom to check if this pages contains other pages
            $pages_level[] = $pages_level[0].'.'.$pageName.'.pages';
          }

          //remove processed page
          array_shift($pages_level);
        }
      }
    }

    foreach ($for as $param)
    {
      $fields = $this->getParameterValue($param, array());

      // add plus in front of our expand columns so they don't show
      if($param == 'list.expand_columns.fields')
      {
        if(!is_array($fields))
        {
          $fields = array($fields);
        }

        foreach($fields as $key => $value)
        {
          $fieldArr[$key] = '+'.$value;
        }
        $fields = $fieldArr;
      }

      // if no fields are defined in generator.yml file, get all default fields
      if (in_array($param, array('list.display', 'edit.display')) && count($fields) == 0)
      {
        foreach ($this->getTableMap()->getColumns() as $column)
        {
          $fields[] = sfInflector::underscore($column->getPhpName());
        }
      }

      // set group info, delimitor is \
      if (!$fields)
      {
        continue;
      }
      elseif (!is_array($fields))
      {
        $columnNames[] = $fields;
        continue;
      }
      else
      {
        // categories?
        if (isset($fields[0]))
        {
          // simulate a default one
          $fields = array('NONE' => $fields);
        }

        if (!$fields)
        {
          $fields = array();
        }
      }


      // add group-name to field-name
      $fieldsWithGroup = array();
      foreach ($fields as $group => $fieldNames)
      {
        foreach ($fieldNames as $fieldName)
        {
          $fieldsWithGroup[] = $group.'\\'.$fieldName;
        }
      }

      $columnNames = array_merge($columnNames, $fieldsWithGroup);
    }

    $groupedColumns = $this->setupGroupedColumns($columnNames);
    return $groupedColumns;
  }

  /**
   * Returns a sub selection of columnsGrouped
   *
   * @param string  $relatedTableFKs   the ForeignKey(s) to the relatedTable
   * @param array() $groupedColumns    Hierarchically arranged array with columns
   * @return subSelection of $groupedColumns
   */
  public function getSubGroupedColumns($relatedTableFKs, $groupedColumns)
  {
    $currentTableFK = $relatedTableFKs;
    $nextTableFKs = null;
    // test if relatedTableName contains mainTable name at begin, if so remove it
    if (false !== strpos($relatedTableFKs, '/'))
    {
      list($currentTableFK, $nextTableFKs) = explode('/', $relatedTableFKs, 2);

      //iterate
      return $this->getSubGroupedColumns($nextTableFKs, $groupedColumns['related'][$currentTableFK]);
    }

    return $groupedColumns['related'][$currentTableFK];

    /*
     //translate foreign-key-name to table-name
     $ColFK = $this->getColumnForFieldName($currentTableFK, $groupedColumns['pk']->getTable()->getPhpName().'Peer');
     $relatedTableName = $ColFK->getRelatedTableName();

     // recursion if remaining foreign-keys
     if ($nextTableFKs)
     {
     return $this->getSubGroupedColumns($nextTableFKs, $groupedColumns['related'][$relatedTableName]);
     }
     else
     {
     return $groupedColumns['related'][$relatedTableName];
     }
     */
  }

  /**
   * returns the hierarchically-grouped-columns-array as a single array, with unique relative-column-names as key
   * PLEASE NOTE2: This will remove the group-hierarchy! (which can be used in edit.display...)
   * PLEASE NOTE2: This removes double columns (so if you defined product/name twice in your list.display it will return only once)
   * TODO? So maybe we should fix this by returing the column-name encapsulated (array($columnname, $group, $column), or we can have an other function for this...
   *
   * @param array() $groupedColumns   Hierarchically arranged array with columns
   * @param boolean $returnInvisible  should invisible fields be returned
   * @param array() $groups           array defining the groups to be returned (keep empty to get all groups)
   */
  public function getListUniqueColumns($groupedColumns, $returnInvisible = false, $groups = array(), $prefix = "", $addPK = true)
  {
    $uniqueColumns = array();

    $columns = $this->getListColumns($groupedColumns, $returnInvisible, $groups, $prefix, $addPK);

    //since the key is unique, we can use the key as THE key for a new array
    foreach ($columns as $column)
    {
      $uniqueColumns[$column->key] = $column;
    }

    return $uniqueColumns;
  }

  /**
   * returns the hierarchically-grouped-columns-array as a single array, with a number as key (which can be used to sort on), and the relative-column-names in the key-property
   * PLEASE NOTE2: This will remove the group-hierarchy! (which can be used in edit.display...)
   * PLEASE NOTE2: This removes double columns (so if you defined product/name twice in your list.display it will return only once)
   * TODO? So maybe we should fix this by returing the column-name encapsulated (array($columnname, $group, $column), or we can have an other function for this...
   *
   * @param array() $groupedColumns   Hierarchically arranged array with columns
   * @param boolean $returnInvisible  should invisible fields be returned
   * @param array() $groups           array defining the groups to be returned (keep empty to get all groups)
   */
  public function getListColumns($groupedColumns, $returnInvisible = false, $groups = array(), $prefix = "", $addPK = true)
  {
    $columnList = array();

    if (!is_array($groups))
    {
      if ($groups != null)
      {
        $groups = array($groups);
      }
      else
      {
        $groups = array();
      }
    }

    if ($addPK && $returnInvisible)
    {
      $pkName = sfInflector::underscore($groupedColumns['pk']->getPhpName());
      $column = $groupedColumns['pk'];
      $column->key = $prefix.$pkName;

      $phpName = $this->getPhpNameForField($column->key);
      $column->setPhpName($phpName);

      $columnList[] = $column;
    }

    // get All Columns of current Table
    foreach($groupedColumns['columns'] as $group => $columnsFromGroup)
    {
      // test if group matches with predefined selection groups
      if ($groups != array())
      {
        if (!in_array($group, $groups)) continue;
      }
      foreach ($columnsFromGroup as $columnName => $column)
      {
        if ($returnInvisible || $column->visible)
        {
          // copy by value
          $newColumn = clone($column);
          // rename key-name
          $newColumn->key = $prefix.$newColumn->key;

          $phpName = $this->getPhpNameForField($newColumn->key);
          $newColumn->setPhpName($phpName);

          $columnList[] = $newColumn;
        }
      }
    }
    // Iterate through all its related tables recursively
    $relatedColumnList = $this->getListRelatedColumns($groupedColumns, $returnInvisible, $groups, $prefix);
    $columnList = array_merge($columnList, $relatedColumnList);

    return $columnList;
  }

  public function getListRelatedColumns($groupedColumns, $returnInvisible = false, $groups = array(), $prefix = "")
  {
    $columnList = array();

    if (!is_array($groups))
    {
      if ($groups != null)
      {
        $groups = array($groups);
      }
      else
      {
        $groups = array();
      }
    }

    // Iterate through all its related tables recursively
    foreach($groupedColumns['related'] as $foreignKey => $relatedGroupedColumns)
    {
      $related_prefix = $prefix.$foreignKey.'/';
      $relatedColumnList = $this->getListColumns($relatedGroupedColumns, $returnInvisible, $groups, $related_prefix, false);
      $columnList = array_merge($columnList, $relatedColumnList);
    }

    return $columnList;
  }

  public function getListRelatedGroupedColumns($groupedColumns)
  {
    $groupedColumnList = array();

    // Iterate through all its related tables recursively
    foreach($groupedColumns['related'] as $relatedName => $relatedGroupedColumns)
    {
      $groupedColumnList[$relatedName] = $relatedGroupedColumns;

      $recursiveRelated = $this->getListRelatedGroupedColumns($relatedGroupedColumns);

      $groupedColumnList = array_merge($groupedColumnList, $recursiveRelated);
    }

    return $groupedColumnList;
  }

  public function getGroupField()
  {
    $group_field = $this->getParameterValue('list.grouping.field', null);
    if ($group_field)
    {
      if (false !== strpos($group_field, '/'))
      {
        if ($this->tableDelimiter != '/' )
        {
          $group_field = str_replace('/', $this->tableDelimiter, $group_field);
        }
      }
//      else
//      {
//        $group_field = sfInflector::underscore($this->getClassName()).$this->tableDelimiter.$group_field;
//      }
    }

    return $group_field;
  }

  public function hasGroupFieldNotInDisplay()
  {
    //TODO: getParamterValue should be replaced by a new method, which removes special characters like =_~ in front from the field-names
    $group_field = $this->getParameterValue('list.grouping.field', null);
    $display = $this->getParameterValue('list.display', array());

    return ($group_field && !in_array($group_field, $display));
  }

  /**
   * Returns HTML code for a column in list mode.
   *
   * @param string  The column name
   * @param array   The parameters
   *
   * @return string HTML code
   */
  public function getColumnListTag($column, $params = array())
  {
    $user_params = $this->getParameterValue('list.fields.'.$column->key.'.params');
    $user_params = is_array($user_params) ? $user_params : sfToolkit::stringToArray($user_params);
    $params      = $user_params ? array_merge($params, $user_params) : $params;

    $type = $column->getCreoleType();

    $columnGetter = $this->getColumnGetter($column, true);

    if ($column->isComponent())
    {
      return "get_component('".$this->getModuleName()."', '".$column->getName()."', array('type' => 'list', '{$this->getSingularName()}' => \${$this->getSingularName()}))";
    }
    else if ($column->isPartial())
    {
      return "get_partial('".$column->getName()."', array('type' => 'list', '{$this->getSingularName()}' => \${$this->getSingularName()}))";
    }
    else if ($type == CreoleTypes::DATE || $type == CreoleTypes::TIMESTAMP)
    {
      $format = isset($params['date_format']) ? $params['date_format'] : ($type == CreoleTypes::DATE ? 'D' : 'U');
      return "($columnGetter !== null && $columnGetter !== '') ?  format_date($columnGetter, \"$format\") : ''";
    }
    //    elseif ($type == CreoleTypes::BOOLEAN)
    //    {
    ////      return "$columnGetter ? image_tag(sfConfig::get('sf_admin_web_dir').'/images/tick.png') : '&nbsp;'";
    //    }
    else
    {
      return "$columnGetter";
    }
  }

  public function getColumnAjaxListDefinition($column, $groupedColumns = array())
  {
    $definition = array();
    $editor = array();

    $definition['header'] = str_replace("'", "\\'", $this->getParameterValue('list.fields.'.$column->key.'.name'));

    $definition['dataIndex'] = str_replace('/', $this->tableDelimiter, $column->key);

    $user_params = $this->getParameterValue('list.fields.'.$column->key.'.params');
    $params = is_array($user_params) ? $user_params : sfToolkit::stringToArray($user_params);

    $listedit = (is_array($this->getParameterValue('list.editable'))) ? $this->getParameterValue('list.editable') : array($this->getParameterValue('list.editable'));
    //check list.editable for editable fields, else check fields.fieldname.params.editable, else go with generator.yml config option else go with the app.yml config
    // TODO: fields.fieldname.params.editable is matching the extjs API, I think credential checking will also work for it, but it isn't nice to have both list.editable and field.editable, what if one says true, other false... makes things complicated
    $editable = (
      in_array($column->key, $listedit) || // if column in list.editable
      !in_array($column->key, $listedit) && ( // else
        (isset($params['editable']) && $params['editable']) || // if isset (list.)fields.fieldname.params.editable and true
        (!isset($params['editable']) && ( // if not set
          (($this->getParameterValue('list.default_editable', null) !== null) && $this->getParameterValue('list.default_editable')) ||
          (($this->getParameterValue('list.default_editable', null) === null) && sfConfig::get('app_sf_extjs_theme_plugin_list_editable', false))
        ))
      )
    ) ? true : false;

    //add ability to set field_type in field config params
    $this->fieldType = (isset($params['field_type'])&&$params['field_type'])?$params['field_type']:$this->getFieldType($column);


    // columns with related data, which are editable
    if (strpos($column->key, '/') !== false && $editable)
    {
      // ForeignFieldColumn plugin handles renderer and editor for the column.  No further processing needed
      $definition = array_merge($definition, $this->getRelatedColumnAjaxListDefinition($column, $groupedColumns));
    }
    else
    {
      $renderParam = (isset($params['renderer']))? $params['renderer'] : false;

      // set CSS id in the first column
      if (isset($params['id'])) $definition['id'] = str_replace("'", "\\'", $params['id']);
      if (isset($params['width'])) $definition['width'] = intval($params['width']);

      $groupBy = ($column->key == $this->getParameterValue('list.grouping.field', null)) ? true : false;

      if (!$groupBy)
      {
        if ($column->isLink() && !$renderParam) $renderParam = 'this.renderLink';
      }

      //default
      $editor['xtype'] = $this->getXtypeForColumn($column);
      $typeRenderer = $this->getRendererForColumn($column);

      switch($this->fieldType)
      {
        case 'date':
          $defaultFormat = sfConfig::get('app_sf_extjs_theme_plugin_format_date', 'm/d/Y'); // TODO set default format from symfony user culture (and replace "-" by "/")
          $editor['format'] = isset($params['date_format']) ? $params['date_format'] : $defaultFormat;
          $editor['minValue'] = isset($params['date_min_value']) ? $params['date_min_value'] : '01/01/00';
          if (isset($params['date_disabled_days']))
          {
            $editor['disabledDays'] = _extjs_array_encode($params['date_disabled_days']);
            $editor['disabledDaysText'] = isset($params['date_disabled_days_text']) ? $params['date_disabled_days_text'] : 'This days are not avaible';
          }
          break;
      }

      //'function(value, params, record, rowIndex, colIndex, store){'..'}';
      $renderCheck = ($renderParam && ($renderParam != 'none')) ? $renderParam : false; //if set to 'none' no renderer should be defined
      if($renderParam == 'none') unset($params['renderer']);
      $renderer = (!$renderParam && !$renderCheck) ? $typeRenderer : $renderCheck;
      if(!empty($renderer)) $definition['renderer'] = $renderer;

      // override generated editor settings with settings in generator.yml
      if ($editable) $definition['editor'] = (isset($params['editor']))?$params['editor']:$editor;

      // if xtype defined in generator.yml overrule default
      if(isset($params['xtype'])) $definition = array_merge($definition,$params);
    } // end local field setup

    //handle plugin settings
    $listplugin = $this->getParameterValue('list.fields.'.$column->key.'.plugin');
    if(isset($listplugin))
    {
      //plugins usually have their own renderer, so remove it unless it's specifically set, then use that
      if(isset($definition['renderer']) && !isset($params['renderer']))
      {
        unset($definition['renderer']);
      }

      //plugins usually have their own editors, so remove it unless it's specifically set, then use that
      if(isset($definition['editor']) && !isset($params['editor']))
      {
        unset($definition['editor']);
      }

      //xtype is set to the value of plugin
      $definition['xtype'] = $listplugin;
    }

    if ($column->isHidden())
    {
      $definition['hidden'] = true;
    }

    // merge with user params if any
    if (is_array($params)) {
      unset($params['combo']);
      $definition = array_merge($definition, $params);
    }

    // hack to handle method renderers
    if(isset($definition['renderer']))
    {
      if(substr($definition['renderer'],0,8)!='function'&&substr($definition['renderer'],0,3)!='new')
      {
        $definition['renderer'] = $definition['renderer'].'.createDelegate(this)';
      }
    }

    return $definition;
  }

  public function getColumnAjaxEditDefinition($column, $groupedColumns = array(), $edit_key = 'edit') //TODO: find out if to keep $edit_key (which contains name in page-hierarchy
  {
    $definition = array();

    $tableName = $this->getTableName();

    $last = strrpos($column->key, '/');
    $relatedTableFKs = substr($column->key, 0, $last);
    $fieldName = str_replace('/', $this->tableDelimiter, $column->key);

    //TODO required
    if (!$column->isPartial())
    {
      $definition['name'] = strtolower($tableName).'['.$fieldName.']';
    }
    $definition['fieldLabel'] = str_replace("'", "\\'", $this->getParameterValue('edit.fields.'.$column->key.'.name')).':';
    $definition['labelSeparator'] = '';

    //TODO: is allow blank processed (with blankText)
    $user_params = $this->getParameterValue('edit.fields.'.$column->key.'.params');
    $params = is_array($user_params) ? $user_params : sfToolkit::stringToArray($user_params);

    // columns with related data
    if (strpos($column->key, '/') !== false)
    {
      // ForeignFieldColumn plugin handles renderer and editor for the column.  No further processing needed
      $definition = array_merge($definition, $this->getRelatedColumnAjaxEditDefinition($column, $groupedColumns));
    }
    else
    {
      // set CSS id in the first column
      //if ($first) $cmOptions['id'] = 'topic'; // I don't think this is necessary, Leon
      if (isset($params['id'])) $definition['id'] = str_replace("'", "\\'", $params['id']);
      if (isset($params['width'])) $definition['width'] = intval($params['width']); //TODO: don't think this is desired for lists, but it probably is for edit-pages

      //default
      $definition['xtype'] = $this->getXtypeForColumn($column);

      switch($this->getFieldType($column))
      {
        case 'date':
          $defaultFormat = sfConfig::get('app_sf_extjs_theme_plugin_format_date', 'm/d/Y'); // TODO set default format from symfony user culture (and replace "-" by "/")
          $definition['format'] = isset($params['date_format']) ? $params['date_format'] : $defaultFormat;
          $definition['minValue'] = isset($params['date_min_value']) ? $params['date_min_value'] : '01/01/00';
          if (isset($params['date_disabled_days']))
          {
            $definition['disabledDays'] = _extjs_array_encode($params['date_disabled_days']);
            $definition['disabledDaysText'] = isset($params['date_disabled_days_text']) ? $params['date_disabled_days_text'] : 'This days are not avaible';
          }
          break;

      }


      // if xtype defined in generator.yml overrule default // TODO why only if xtype defined? I disabled this (Leon) REMOVE THESE LINES IF EVERYTHING OK/AGREE
      //if(isset($params['xtype']))
      $definition = array_merge($definition, $params);

      // TODO: think about isHidden, for foreign-fields should it look at it, and if so, should it look at the foreign-key or foreign_key/field column?
      if ($column->isHidden())
      {
        $definition['hidden'] = true;
      }
      else if ($column->isNotNull())
      {
        $definition['allowBlank']   = false;
        $definition['itemCls']      = 'required';
        $definition['fieldLabel']   .= '<em>required</em>';
        $definition['blankText']    = 'This field is required';
      }

    } // end local field setup

    return $definition;
  }

  public function getColumnAjaxFilterDefinition($column, $groupedColumns = array())
  {
    $definition = array();

    $tableName = $this->getTableName();

    $last = strrpos($column->key, '/');
    $relatedTableFKs = substr($column->key, 0, $last);
    $fieldName = str_replace('/', $this->tableDelimiter, $column->key);

    if (!$column->isPartial())
    {
      $definition['name'] = 'filters['.$fieldName.']';
    }
    $definition['fieldLabel'] = str_replace("'", "\\'", $this->getParameterValue('edit.fields.'.$column->key.'.name')).':';
    $definition['labelSeparator'] = '';
    $definition['filter'] = true;

    $user_params = $this->getParameterValue('list.fields.'.$column->key.'.params');
    $params = is_array($user_params) ? $user_params : sfToolkit::stringToArray($user_params);
    $user_params = $this->getParameterValue('list.fields.'.$column->key.'.params');

    //add ability to set field_type in field config params, this should be move to $this->getFieldType()
    $this->fieldType = (isset($params['field_type'])&&$params['field_type'])?$params['field_type']:$this->getFieldType($column);

    // if combo set in the generator create a combo that gets unique values for the local column
    $key = (strpos($column->key, '/')) ? str_replace('/','-',$column->key) : $column->key ;
    //foreign keys that are not dates and with filter_field that is not filterfield and local columns with filter_filed that is combo
    $combo = ($this->fieldType != 'date' && $this->fieldType != 'boolean' && strpos($column->key, '/') !== false)?true:false;
    $combo = (isset($params['filter_field']) && $params['filter_field'] == 'textfield')?false:$combo;
    $combo = (isset($params['filter_field']) && $params['filter_field'] == 'combo')?true:$combo;
    if($combo)
    {
      $definition['xtype'] = 'filtertwincombobox';
      $definition['url'] = $this->controller->genUrl($this->getModuleName().'/jsonCombo');
      $definition['valueField'] = $key;
      $definition['hiddenName'] = $key;
      $definition['displayField'] = $key;
      $definition['typeAhead'] = false;
      $definition['sortField'] = $key;
      $definition['groupField'] = $key;
      $definition['pageSize'] = 0;
      $definition['minListWidth'] = 150;
      $definition['chained'] = $this->getParameterValue('filterpanel.params.chained') ? 'query' : null;
      $definition['stateful'] = $this->getParameterValue('filterpanel.params.saveState') ? true: null;
      $definition['stateEvents'] = $this->getParameterValue('filterpanel.params.saveState') ? array('select','clear'): null;
      $definition['stateId'] = $this->getParameterValue('filterpanel.params.saveState') ? $this->getModuleName().'-'.$key: null;
    }
    else
    {
      // set CSS id in the first column
      if (isset($params['id'])) $definition['id'] = str_replace("'", "\\'", $params['id']);

      //default
      $definition['xtype'] = $this->getXtypeForColumn($column);

      switch($this->fieldType)
      {
        case 'date':
          $defaultFormat = sfConfig::get('app_sf_extjs_theme_plugin_format_date', 'm/d/Y'); // TODO set default format from symfony user culture (and replace "-" by "/")
          $definition['format'] = isset($params['date_format']) ? $params['date_format'] : $defaultFormat;
          $definition['minValue'] = isset($params['date_min_value']) ? $params['date_min_value'] : '01/01/00';
          if (isset($params['date_disabled_days']))
          {
            $definition['disabledDays'] = _extjs_array_encode($params['date_disabled_days']);
            $definition['disabledDaysText'] = isset($params['date_disabled_days_text']) ? $params['date_disabled_days_text'] : 'This days are not avaible';
          }
          break;
        case 'boolean':
          //listener to filter when checkbox is checked or unchecked
          if($this->getParameterValue('filterpanel.params.saveState'))
          {
            $definition['listeners'] = array(
              'check' => "function(){Ext.state.Manager.set(this.name, this.checked);this.ownerCt.buttons[0].handler();}"
            );
            $definition['checked'] = "Ext.state.Manager.get('".$definition['name']."', '')";
          }
          else
          {
            $definition['listeners'] = array(
              'check' => "function(){this.ownerCt.buttons[0].handler();}"
            );
          }
          $definition['listeners']['reset'] = "function(){this.setValue(false);}";
          break;
        case 'string':
          //listener to filter when enter is pressed
          $definition['listeners'] = array(
            'specialkey' => "function(f,e){if(f.getValue() != '' && e.getKey() ==13)this.ownerCt.buttons[0].handler();}"
          );
          break;

      }
    } // end local field setup

    if(isset($params['width'])) unset($params['width']);
    return array_merge($definition, $params);
  }

  // maybe move to helper
  function getXtypeForColumn($column)
  {
    $xtype = 'textfield';

    $fieldType = ($this->fieldType)?$this->fieldType:$this->getFieldType($column);

    switch($fieldType)
    {
      case 'date':
        $xtype = 'datefield';
        break;

      case 'boolean':
        $xtype = sfConfig::get('app_sf_extjs_theme_plugin_checkbox_type', 'checkbox');
        break;

      case 'text':
        $xtype = 'textarea';
        break;

      case 'float':
      case 'int':
        $xtype = 'numberfield';
        break;
    }

    return $xtype;
  }

  // maybe move to helper
  function getRendererForColumn($column)
  {
    $renderer = null;

    switch($this->getFieldType($column))
    {
      case 'date':
        $defaultFormat = sfConfig::get('app_sf_extjs_theme_plugin_format_date', 'm/d/Y'); // TODO set default format from symfony user culture (and replace "-" by "/")
        $format = isset($params['date_format']) ? $params['date_format'] : $defaultFormat;
        $renderer = (isset($editor['format']) && $editor['format'] != $defaultFormat) ? 'function(value){ Ext.util.Format.date(value, \''.$format.'\') }' : 'this.formatDate';
        break;

      case 'boolean':
        $renderer =  'this.formatBoolean';
        break;

      case 'text':
        $renderer =  'this.formatLongstring';
        break;

      case 'float':
      case 'int':
        $renderer =  'this.formatNumber';
        break;
    }

    return $renderer;
  }

  function getRelatedColumnAjaxListDefinition($column, $groupedColumns)
  {
    // TODO: do foreign thing here for drop-down comboboxes
    if (strpos($column->key, '/') !== false)
    {
      $fields = explode('/', $column->key);

      $last                 = strrpos($column->key, '/');
      $relatedTableFKs      = substr($column->key, 0, $last);
      $columnname           = substr($column->key, $last + 1);
      $relatedTableGrouped  = $this->getSubGroupedColumns($relatedTableFKs, $groupedColumns);
      $relatedTablePK       = $relatedTableGrouped['pk'];
      $relatedTableName     = $relatedTablePK->getTableName();
      $relatedModuleName    = $this->getParameterValue('related_tables.'.$relatedTableName.'.module_name') ? $this->getParameterValue('related_tables.'.$relatedTableName.'.module_name') : $relatedTableName;
      $relatedFKColumn      = $this->getColumnForFieldName($fields[0]);


      $ffcolumn['xtype'] = 'foreignfieldcolumn';
      $ffcolumn['url'] = $this->controller->genUrl($this->getModuleName().'/jsonAutocomplete?class='.$relatedTableName);
      $ffcolumn['valueField']   = $this->getRelatedFieldName($relatedTablePK);
      $ffcolumn['displayField'] = $this->getRelatedFieldName($column);
      $ffcolumn['dataIndex']    = str_replace('/', $this->tableDelimiter, $relatedTableFKs);
      $ffcolumn['preloadedField'] = str_replace('/', $this->tableDelimiter, $column->key);
      $ffcolumn['relatedTableName'] = $relatedTableName;
      $ffcolumn['relatedModuleName'] = $relatedModuleName;
      $ffcolumn['relatedFieldName'] = $columnname;
      $ffcolumn['queryParam'] = 'filters['.str_replace('/', $this->tableDelimiter, $column->key).']';
      $ffcolumn['sortField'] = str_replace('/', $this->tableDelimiter, $column->key);

      //make sure our columnConfig in the generator.yml overrides the generated values
      $columnConfig = $this->getParameterValue('list.fields.'.$column->key.'.params.combo');
      if($columnConfig) $ffcolumn = array_merge($ffcolumn,$columnConfig);

      if(isset($ffcolumn['store']))
      {
        $ffcolumn['comboConfig']= array(
          'editable' => false,
          'mode' => 'local',
          'pageSize' => 0
        );
      }
      //make sure our comboConfig in the generator.yml overrides the generated values
      $comboConfig = $this->getParameterValue('list.fields.'.$column->key.'.params.combo.comboConfig');
      if($comboConfig) $ffcolumn['comboConfig'] = array_merge($ffcolumn['comboConfig'], $comboConfig);

      return $ffcolumn;

      /*
       engCol = new Ext.ux.grid.ForeignFieldColumn({
       url:'lbrequest/engList',
       valueField:'value',
       displayField:'display',
       header : 'Assigned',
       dataIndex : 'eng_id',
       comboConfig: {
       minListWidth: 150
       }
       });

       statusCol = new Ext.ux.grid.ForeignFieldColumn({
       header : 'Status',
       dataIndex : 'status',
       store: [[0,'New'],[1,'Pending'],[2,'Cancelled'],[3,'Closed']]
       });

       statusCol2 = new Ext.ux.grid.ForeignFieldColumn({
       header : 'Status2',
       dataIndex : 'status2',
       store: ['New','Pending','Cancelled','Closed']
       });

       */
    }
  }

  function getRelatedColumnAjaxEditDefinition($column, $groupedColumns)
  {
    // TODO: do foreign thing here for drop-down comboboxes
    if (strpos($column->key, '/') !== false)
    {
      $fields = explode('/', $column->key);

      $last                 = strrpos($column->key, '/');
      $columnname           = substr($column->key, $last + 1);
      $relatedTableFKs      = substr($column->key, 0, $last);
      $relatedTableGrouped  = $this->getSubGroupedColumns($relatedTableFKs, $groupedColumns);
      $relatedTablePK       = $relatedTableGrouped['pk'];
      $relatedTableName     = $relatedTablePK->getTableName();
      $relatedModuleName    = $this->getParameterValue('related_tables.'.$relatedTableName.'.module_name') ? $this->getParameterValue('related_tables.'.$relatedTableName.'.module_name') : $relatedTableName;
      $relatedFKColumn      = $this->getColumnForFieldName($fields[0]);

      $tableName = $this->getTableName();
      $fieldName = str_replace('/', $this->tableDelimiter, $column->key);

      //replace default name
      $ffcolumn['name'] = strtolower($tableName).'['.$relatedTableFKs.']';

      $ffcolumn['xtype'] = 'comboboxautoload';
      $ffcolumn['url'] = $this->controller->genUrl($this->getModuleName().'/jsonAutocomplete?class='.$relatedTableName);
      $ffcolumn['valueField']   = $this->getRelatedFieldName($relatedTablePK);
      $ffcolumn['displayField'] = $this->getRelatedFieldName($column);
      $ffcolumn['dataIndex']    = str_replace('/', $this->tableDelimiter, $relatedTableFKs);
      $ffcolumn['preloadedField'] = strtolower($tableName).'['.$fieldName.']';
      $ffcolumn['relatedTableName'] = $relatedTableName;
      $ffcolumn['relatedModuleName'] = $relatedModuleName;
      $ffcolumn['relatedFieldName'] = $columnname;
      $ffcolumn['queryParam'] = 'filters['.str_replace('/', $this->tableDelimiter, $column->key).']';
      $ffcolumn['sortField'] = str_replace('/', $this->tableDelimiter, $column->key);

      //make sure our columnConfig in the generator.yml overrides the generated values
      $fieldConfig = $this->getParameterValue('list.fields.'.$column->key.'.params.combo');
      if($fieldConfig) $ffcolumn = array_merge($ffcolumn,$fieldConfig);

      if(isset($ffcolumn['store']))
      {
        $ffcolumn['editable'] = false;
        $ffcolumn['mode'] = 'local';
        $ffcolumn['pageSize'] = 0;
      }

      //make sure our comboConfig in the generator.yml overrides the generated values
      $comboConfig = $this->getParameterValue('list.fields.'.$column->key.'.params.combo.comboConfig');
      if($comboConfig) $ffcolumn = array_merge($ffcolumn['comboConfig'], $comboConfig);

      if ($relatedFKColumn->isNotNull())
      {
        $ffcolumn['allowBlank']   = false;
        $ffcolumn['itemCls']      = 'required';
        $ffcolumn['fieldLabel']   = str_replace("'", "\\'", $this->getParameterValue('edit.fields.'.$column->key.'.name')).':'.'<em>required</em>';
        $ffcolumn['blankText']    = 'This field is required';
      }


      return $ffcolumn;
    }
  }

  /**
   * returns Ext-dataType
   * See http://extjs.com/deploy/dev/docs/output/Ext.data.Record.html#create
   *
   * @param ColumnMap $column
   * @return string containing the dataType
   */
  function getFieldTypeForReader($column)
  {
    $user_params = $this->getParameterValue('list.fields.'.$column->key.'.params');
    $params = is_array($user_params) ? $user_params : sfToolkit::stringToArray($user_params);
    $dataType = (isset($params['field_type'])&&$params['field_type'])?$params['field_type']:$this->getFieldType($column);

    switch($dataType)
    {
      case 'text':
        $dataType = 'string';
        break;
    }

    return $dataType;
  }

  function getFieldType($column)
  {
    $type = $column->getCreoleType();

    //default
    $fieldType = 'string';

    switch($type)
    {
      case CreoleTypes::DATE:
      case CreoleTypes::TIMESTAMP:
        $fieldType = 'date';
        break;

      case CreoleTypes::BOOLEAN:
        $fieldType = 'boolean'; // TODO: this should probably be boolean, not bool, remove this comment next time you see it and everything works well
        break;

      case CreoleTypes::TEXT:
      case CreoleTypes::LONGVARCHAR:
        $fieldType = 'text'; // WARNING this is not a valid fieldType for ExtJS DataReader
        break;

      case CreoleTypes::FLOAT:
      case CreoleTypes::DOUBLE:
      case CreoleTypes::DECIMAL:
      case CreoleTypes::NUMERIC:
      case CreoleTypes::REAL:
        $fieldType = 'float';
        break;

      case CreoleTypes::INTEGER:
      case CreoleTypes::TINYINT:
      case CreoleTypes::SMALLINT:
      case CreoleTypes::BIGINT:
        $fieldType = 'int';
        break;
    }

    return $fieldType;
  }

  public function getPhpNameForField($field, $peerName = null, $flags = array())
  {
    $fields = explode('/', $field);

    $lastPeerClassName = $this->getPeerClassName();
    if ($peerName)
    {
      $lastPeerClassName = $peerName;
    }

    $phpName = "";
    if ((!in_array('_', $flags)) && ($field!='*'))// no-partial or row expander
    {
      //get PHP-name for (all) foreign tables (from foreign-key)
      for ($i = 0; $i < count($fields); $i++)
      {
        $currentField = $fields[$i];
        list($currentField, $currentFlags) = $this->splitFlag($currentField);

        if (!in_array('_', $currentFlags)) // no-partial
        {
          $tableMap = call_user_func(array($lastPeerClassName, 'getTableMap'));
          // search the matching column for this key
          $found = false;
          foreach ($tableMap->getColumns() as $column)
          {
            if ($column->getColumnName() == strtoupper($currentField)) // TODO not tested if strtoupper is always correct, maybe both strtoupper...
            {
              $found = true;
              break;
            }
          }

          // if column not found, it is a partial or a customMethod
          if (!$found)
          {
            // not a "real" column, but we generate one here
            $flagPrefix = '';
            foreach ($flags as $flag)
            {
              $flagPrefix .= $flag;
            }
            $name = strtoupper($flagPrefix.$currentField);

            $tableMap = call_user_func(array($lastPeerClassName, 'getTableMap'));
            $column = new ColumnMap($name, $tableMap);
            //            $methodName = (isset($fields[1])) ? $fields[1] : $fields[0]; // This is nonsense (, by the way what I did before as well, it should be current-field)
            $column->setPhpName(sfInflector::camelize(sfInflector::camelize($currentField)));
            //TODO set a flag this is a partial/custom method, WHY?
          }
        }

        // if not last field, return table getter
        if ($i != count($fields) - 1)
        {
          $relatedTableName = $column->getRelatedTableName();
          $relatedTable = $this->getMap()->getDatabaseMap()->getTable($relatedTableName);

          $phpName .= $relatedTable->getPhpName();

          // if this column is not the only column refering to this foreing table add text
          if ($this->isMultipleFK($column)) {
            $phpName .= 'RelatedBy'.$column->getPhpName();
          }

          $phpName .= '::';

          $lastPeerClassName = $relatedTable->getPhpName().'Peer';
        }
        //if last field return column getter
        else
        {
          $phpName .= $column->getPhpName();
        }
      }
    }
    else //partial
    {
      $phpName .= sfInflector::camelize($field);
    }

    return $phpName;
  }

  /** This method overwrites the original method so it can handle foreign-field by providing a peerName
   */
  public function getAdminColumnForField($field, $flags = array(), $peerName = null)
  {
    $fields = explode('/', $field);

    $lastPeerClassName = $this->getPeerClassName();
    if ($peerName)
    {
      $lastPeerClassName = $peerName;
    }

    $phpName = "";
    if ((!in_array('_', $flags)) && ($field!='*'))// no-partial or row expander
    {
      //get PHP-name for (all) foreign tables (from foreign-key)
      for ($i = 0; $i < count($fields); $i++)
      {
        $currentField = $fields[$i];
        list($currentField, $currentFlags) = $this->splitFlag($currentField);

        if (!in_array('_', $currentFlags)) // no-partial
        {
          $tableMap = call_user_func(array($lastPeerClassName, 'getTableMap'));
          // search the matching column for this key
          $found = false;
          foreach ($tableMap->getColumns() as $column)
          {
            if ($column->getColumnName() == strtoupper($currentField)) // TODO not tested if strtoupper is always correct, maybe both strtoupper...
            {
              $found = true;
              break;
            }
          }

          // if column not found, it is a partial or a customMethod
          if (!$found)
          {
            // not a "real" column, but we generate one here
            $name = strtoupper($currentField);

            $tableMap = call_user_func(array($lastPeerClassName, 'getTableMap'));
            $column = new ColumnMap($name, $tableMap);
          }
        }

        // if not last field, return table getter
        if ($i != count($fields) - 1)
        {
          $relatedTableName = $column->getRelatedTableName();
          $relatedTable = $this->getMap()->getDatabaseMap()->getTable($relatedTableName);

          $lastPeerClassName = $relatedTable->getPhpName().'Peer';
        }
      }
    }

    $phpName = $this->getPhpNameForField($field, $peerName, $flags);

    return new sfExtjsAdminColumn($phpName, $this->getColumnForPhpName($phpName, $lastPeerClassName, $field, $flags), $flags);
  }

  /** This method overwrites the original method, so it can handle phpNames containing classNames
   *
   * returns a column phpName or null if none was found
   */
  public function getColumnForPhpName($fullPhpName, $peerName = null, $field = '', $flags = array())
  {
    //strip
    $phpNames = explode('::', $fullPhpName);
    $phpName = $phpNames[count($phpNames) - 1];

    if ($peerName == null)
    {
      $peerName = $this->getPeerClassName();

      if (count($phpNames)>1)
      {
        $className = $phpNames[count($phpNames) - 2];
        $peerName = $className."Peer";
      }
    }

    $tableMap = call_user_func(array($peerName, 'getTableMap'));

    // search the matching column for this PHPname
    foreach ($tableMap->getColumns() as $column)
    {
      if ($column->getPhpName() == $phpName)
      {
        return $column;
      }
    }

    // not a "real" column, but we generate one here
    $flagPrefix = '';
    foreach ($flags as $flag)
    {
      $flagPrefix .= $flag;
    }
    $name = strtoupper($flagPrefix.$field);
    //TODO SET A FLAG TELLING THIS IS A CUSTOM METHOD/PARTIAL
    $col = new ColumnMap($name, $tableMap);
    return $col;
  }

  /** This method returns the column from its (relative) fieldname
   * relative by providing foreign-keys
   *
   * returns the column from this (relative) fieldname
   */
  public function getColumnForFieldName($fieldName, $peerName = null)
  {
    if ($peerName == null)
    {
      $peerName = $this->getPeerClassName();
    }
    $tableMap = call_user_func(array($peerName, 'getTableMap'));

    // recursion needed?
    if (false !== strpos($fieldName, '/'))
    {
      list($firstFieldName, $nextFieldNames) = explode('/', $fieldName, 2);

      foreach ($tableMap->getColumns() as $column)
      {
        if ($column->getColumnName() == strtoupper($firstFieldName))
        {
          $relatedTableName = $column->getRelatedTableName();
          $relatedTable = $this->getMap()->getDatabaseMap()->getTable($relatedTableName);

          $peerName = $relatedTable->getPhpName().'Peer';
          return $this->getColumnForFieldName($nextFieldNames, $peerName);
        }
      }
    }
    // find and return column form current table
    else
    {
      // search the matching column for this PHPname
      foreach ($tableMap->getColumns() as $column)
      {
        if ($column->getColumnName() == strtoupper($fieldName))
        {
          return $column;
        }
      }
    }

    // column not found in this table, so we return null
    throw new Exception('field "'.$fieldName.'" not found in Peer "'.$peerName.'"');
    return null;
  }

  public function getTableName($underscore = true)
  {
    $tableName = $this->getTableMap()->getPhpName();

    return $underscore ? sfInflector::underscore($tableName) : $tableName;
  }

  public function getRelatedFieldName($column)
  {
    $tableName = $column->getTableName();

    $columnname = $column->getName();
    $last = strrpos($column->getName(), '/');
    if ($last) {
      $columnname = substr($column->getName(), $last + 1);
    }

    $relatedFieldName = $tableName.$this->tableDelimiter.$columnname;

    return $relatedFieldName;
  }

  public function sortColumns($columns)
  {
    // "sort" output on index, since index should be unique
    // first create a new array
    $sorted = array();
    foreach ($columns as $column)
    {
      $sorted[$column->index] = $column;
    }
    // do real sortining
    ksort($sorted);

    // return sorted array back
    return $sorted;
  }

  /**
   * Determine if this column is a foreign key that refers to the
   * same table as another foreign key column in this table.
   */
  // METHOD below should be used FROM COLUMN.PHP (/symfony/lib/vendor/propel-generator/classes/propel/engine/database/model/Column.php)
  public function isMultipleFK($column)
  {
    // test if this column is a foreign-key
    if ($relatedTableName = $column->getRelatedTableName()) {

      //this columnName
      $columnName = $column->getColumnName();

      $parentTable = $column->getTable();
      $otherColumns = $parentTable->getColumns();

      // TODO this should be a method of the TableMap
      foreach ($otherColumns as $otherColumn) {
        if ($otherColumn === $column) continue; // I said otherColumns, not this column

        // test if this column is the only FK to its relatedTable
        if ($otherColumn->getRelatedTableName() == $relatedTableName) return true;
      }
    }

    // No multiple foreign keys.
    return false;
  }
}