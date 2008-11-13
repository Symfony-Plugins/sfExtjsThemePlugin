<?php
/**
 * Admin generator column for ExtJs generator.
 *
 * @package    Extjs2DbFinderAdminGeneratorThemePlugin
 * @subpackage generator
 * @author     Leon van der Ree, Benjamin Runnels
 * @version    0.1
 */
class sfExtjsAdminColumn extends sfAdminColumn
{
  public $displayArr;

  public function __construct($phpName, $column = null, $flags = array())
  {
    parent::__construct($phpName,$column,$flags);

//    $this->phpName = $phpName;
//    $this->column  = $column;
//    $this->flags   = (array) $flags;
  }

  /**
   * Returns true if the column is hidden.
   *
   * @return boolean true if the column is hidden, false otherwise
   */
  public function isHidden()
  {
    return in_array('-', $this->flags) ? true : false;
  }

  /**
   * Returns true if the column is invisible (not part of the grids columns, usefull for renderers, partials and templates).
   *
   * @return boolean true if the column is very-hidden, false otherwise
   */
  public function isInvisible()
  {
    return in_array('+', $this->flags) ? true : false;
  }

  /**
   * Returns true if the column is a plugin placeholder (row expander, rowactions, etc...)
   *
   * @return boolean true if the column is a plugin placeholder, false otherwise
   */
  public function isPlugin()
  {
    return in_array('^', $this->flags) ? true : false;
  }

  /**
   * return the internal column
   *
   * @return ColumnMap
   */
  public function getColumn()
  {
    return $this->column;
  }

  /**
   * Sets(/changes) the phpName after construction
   *
   * @param string $phpName
   */
  public function setPhpName($phpName)
  {
    $this->phpName = $phpName;
  }

}