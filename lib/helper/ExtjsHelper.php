<?php

//TODO: remove initial hardcoded spaces (in argument of _extjs_write_class), maybe also replace string for integer, which defines number of spaces

function extjs_data_reader($options = array(), $mapping = array(), $type = 'array')
{
  $type = strtolower($type);

  return _extjs_write_class('data.'.ucfirst($type).'Reader', array($options, $mapping), true);
}

function _extjs_convert_array_data($data, $type = 'array')
{
  $type = strtolower($type);

  switch ($type)
  {
    case 'json':
      $js = _extjs_property_encode($data);
      break;

    default:
      $js = _extjs_array_encode($data);
      break;
  }

  return $js;
}

function extjs_data_store($options, $type = '')
{
  $type = strtolower($type);

  return _extjs_write_class('data.'.ucfirst($type).'Store', array($options), true);
}

// TODO: check http://extjs.com/forum/showthread.php?t=9171&highlight=overwrite+css
// it is about autocompletion, might contain something usefull about being able to enter new values (not only the ones that are already in the foreign-store)
function extjs_combo_options($options)
{
  $defaultOptions = array(
    'xtype'         => sfConfig::get('app_sf_extjs_theme_plugin_combo_box_xtype', 'combo'),

    'displayField'  => 'REQUIRED',
    'queryParam'    => 'filters[REQUIRED]',

    'mode'          => 'remote',
    'pageSize'      => sfConfig::get('app_sf_extjs_theme_plugin_list_max_per_page', 20),
    'typeAhead'     => true,
    'minChars'      => 2,
    'triggerAction' => 'all',
    'lazyRender'    => true,
    'listClass'     => 'x-combo-list-small'
  );

  $options = array_merge($defaultOptions, $options);

  //$class = sfConfig::get('app_sf_extjs_theme_plugin_combo_box_class', 'form.ComboBox'); // TODO: OBSOLETE should be using xtypes!!!
  return $options;

}

function _extjs_write_class($name, $arguments = array(), $constructor = false, $expander = false, $spaces = '    ')
{
  $js = ($constructor ? 'new ' : '').'Ext.'.$name;

  if (!empty($arguments))
  {
    $arguments = is_array($arguments) ? $arguments : array($arguments);
    $first = true;

    $js .= '(';
    foreach ($arguments as $arg)
    {
      $js .= (!$first ? $spaces."  ".', ' : '')._extjs_property_encode($arg, $spaces, $expander)."\n";
      $first = false;
    }
    $js .= $spaces.')';
  }

  return $js;
}

function extjs_convert_propel_type($type)
{
  //TODO: CHECK TYPES, apparantly integer does not exists but is int already... are they maybe switched?
  //I now return $type, if not found.
  if (in_array($type, array('date', 'datetime', 'time', 'timestamp')))
  {
    return 'date';
  }

  if ($type == 'boolean')
  {
    return 'boolean';
  }

  if (in_array($type, array('double', 'float')))
  {
    return 'float';
  }

  if ($type == 'integer')
  {
    return 'int';
  }

  return $type;
//  return 'string';
}

function _extjs_property_encode($array, $spaces = '', $expander = false)
{
  // indentation
  $spaces .= "  ";

  if (count($array) > 0)
  {
    $prefix = '{';
    $suffix = '}';
    $fixPF = false;
    $code = null;

    // TODO: Check boolean values, they now are outputed as '1', instead of true
    foreach ($array as $key => $value)
    {
      $deb = ($code ? ', '."\n".$spaces."  " : ''."\n".$spaces."  ").(!is_int($key) ? $key.': ' : '');
      if (is_array($value))
      {
        if (!$fixPF)
        {
          $prefix = ($expander)?"[\n$spaces  expander,":'[';
          $suffix = "\n".$spaces.']';
        }
        $subcode = _extjs_property_encode($value, $spaces);
        $code .= $deb.$subcode;
      }
      elseif($key!='items' && $key!='buttons')
      {
        $prefix = '{';
        $suffix = "\n".$spaces.'}';
        $fixPF = true;
        $code .= $deb.(_extjs_quote_except($key, $value) ? '\''.$value.'\'' : $value);
      }
      else
      {
        $prefix = '{';
        $suffix = "\n".$spaces.'}';
        $fixPF = true;
        $code .= $deb.(_extjs_quote_except($key, $value) ? $value : $value);
      }
    }

    return $prefix.$code.$suffix;
  }

  return null;
}

function _extjs_quote_except($key, $value)
{
  $quoteExcept = sfConfig::get('extjs_quote_except');

  if (is_int($key))
  {
    return false;
  }
  else
  {
    foreach ($quoteExcept['key'] as $except)
    {
      if ($key == $except)
      {
        return false;
      }
    }
  }


  if (is_int($value) || is_array($value))
  {
    return false;
  }
  else
  {
    foreach ($quoteExcept['value'] as $except)
    {
      if (substr($value, 0, strlen($except)) == $except)
      {
        return false;
      }
    }
  }

  return true;
}

function _extjs_array_encode($array)
{
  if (count($array) > 0)
  {
    $code = null;
    foreach ($array as $key => $value)
    {
      $deb = ($code ? ', ' : '');
      if (is_array($value))
      {
        $code .= $deb._extjs_array_encode($value);
      }
      else
      {
        $code .= $deb.(is_int($value) ? $value : '\''.$value.'\'');
      }
    }

    return '[ '.$code.' ]';
  }

  return null;
}

function extjs_escape_json_string($string)
{
  $escape = array("\r\n" => '\n',
                  "\r" => '\n',
                  "\n" => '\n',
                  "'" => "\\'",
                  '"' => '\"',
                  '\"' => '\\"');
  return str_replace(array_keys($escape), array_values($escape), $string);
}
?>
