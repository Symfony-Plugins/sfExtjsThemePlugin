[?php

/**
 * <?php echo $this->getGeneratedModuleName() ?> actions.
 *
 * @package    ##PROJECT_NAME##
 * @subpackage <?php echo $this->getGeneratedModuleName() ?>

 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: actions.class.php 3501 2007-02-18 10:28:17Z fabien $
 */
<?php
  $tableDelimiter = sfConfig::get('app_sf_extjs_theme_plugin_table_delimiter', '-');
?>
class <?php echo $this->getGeneratedModuleName() ?>Actions extends sfActions
{
  public function sendAjaxResponse()
  {
    $data = array(
      'success' => $this->success,
      'id'      => $this->id,
      'title'   => $this->title
    );

    if($this->message) $data['message'] = $this->message;
    $result = json_encode($data);

    $this->getResponse()->setHttpHeader("X-JSON", '()');

    return $this->renderText($result);
  }

  public function executeJsonCombo()
  {
    $group = explode('-',$this->getRequestParameter('group'));
    $this->groupby = array($this->getRequestParameter('group')=>true);
    $this->forwardIf(!$this->groupby,'<?php echo $this->getModuleName() ?>','ajaxFailed');

    if($this->getRequest()->hasParameter('filter'))
    {
      $filters = $this->getRequestParameter('filters');
      $this->getUser()->getAttributeHolder()->add($filters, 'sf_admin/<?php echo $this->getSingularName() ?>/filters');
    }
    else
    {
      $this->getUser()->getAttributeHolder()->removeNamespace('sf_admin/<?php echo $this->getSingularName() ?>/filters');
    }
    $this->filters = $this->getUser()->getAttributeHolder()->getAll('sf_admin/<?php echo $this->getSingularName() ?>/filters');

    $c = new Criteria();
    $this->addGroupCriteria($c);
    $this->addFiltersCriteria($c);
    $rs = <?php echo $this->getClassName() ?>Peer::doSelectRS($c);
    $rs->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $jsonArr = array();
    while($rs->next())
    {
      $resultRow = $rs->getRow();
      foreach( $resultRow as $key => $value)
      {
        if(!is_null($value))$jsonArr[][$this->getRequestParameter('group')] = $value;
      }
    }

    $json =  '{"totalCount":'.count($jsonArr).', "data":'.json_encode($jsonArr).'}';

    return $this->renderText($json);
  }

  public function executeIndex()
  {
    return $this->forward('<?php echo $this->getModuleName() ?>', 'list');
  }

  /*
   * the action which requests the values for drop-down boxes of a specific (related) class.
   * this function does not affect the user-session, so sorting and your filters keep working
   */
  public function executeJsonAutocomplete()
  {
    // Require class
    if (!$this->hasRequestParameter('class'))
    {
      throw new sfException(sprintf('Error, the JsonAutocomplete page requires a class-name as argument'));
    }
    $class = sfInflector::camelize($this->getRequestParameter('class'));

    // Hack to eliminate misspelled camelize classes (like sfGuard...)
    if(strpos($class, 'Sf') === 0)
    {
      $class = str_replace('Sf', 'sf', $class);
    }

    $limit = $this->getRequestParameter('limit', <?php echo $this->getParameterValue('list.max_per_page', sfConfig::get('app_sf_extjs_theme_plugin_list_max_per_page', 20)) ?>);
    $page = floor($this->getRequestParameter('start', 0) / $limit)+1;

    // filter, define namespace to autocomplete, to not disturb normal filters and sorting of this module
    $namespace = "autocomplete";

    $this->processSort(strtolower($this->getRequestParameter('dir')), $namespace);

    $this->getUser()->getAttributeHolder()->removeNamespace('sf_admin/'.$namespace.'/filters');
    $this->processFilters($namespace);

    $this->filters = $this->getUser()->getAttributeHolder()->getAll("sf_admin/$namespace/filters");

    // pager
    $this->pager = new sfPropelPager($class, $limit);
    $c = new Criteria();

    $this->addSortCriteria($c, $namespace);
    $this->addFiltersCriteria($c, $namespace);
    $this->pager->setCriteria($c);
    $this->pager->setPage($page);

    $this->pager->init();

    $result = $this->json_encode_autocomplete_pager_results($this->pager);

    $this->getResponse()->setHttpHeader("X-JSON", '()'); // set a header, although it is empty...

    return $this->renderText($result);
  }

  //JSON-data for the list
  public function executeJsonList()
  {
    $limit = $this->getRequestParameter('limit', <?php echo $this->getParameterValue('list.max_per_page', sfConfig::get('app_sf_extjs_theme_plugin_list_max_per_page', 20)) ?>);
    $page = floor($this->getRequestParameter('start', 0) / $limit)+1;
    $namespace = $this->getRequestParameter('namespace', '<?php echo $this->getSingularName() ?>');

    $this->processSort(strtolower($this->getRequestParameter('dir')),$namespace);
    $this->processFilters($namespace);
    $this->filters = $this->getUser()->getAttributeHolder()->getAll('sf_admin/'.$namespace.'/filters');

    // pager
    $this->pager = new sfPropelPager('<?php echo $this->getClassName() ?>', $limit);
    $c = new Criteria();
    $this->addSortCriteria($c,$namespace);
    $this->addFiltersCriteria($c,$namespace);
    $this->pager->setCriteria($c);
    $this->pager->setPage($page);
<?php $peerMethod = $this->getParameterValue('list.peer_method') ? $this->getParameterValue('list.peer_method') : $this->getParameterValue('peer_method') ? $this->getParameterValue('peer_method') : 'doSelectJoinAll';
    if (is_callable(array($this->getPeerClassName(), $peerMethod))): ?>
    $this->pager->setPeerMethod('<?php echo $peerMethod ?>');
<?php endif; ?>

<?php $peerCountMethod = $this->getParameterValue('list.peer_count_method') ? $this->getParameterValue('list.peer_count_method') : $this->getParameterValue('peer_count_method') ? $this->getParameterValue('peer_count_method') : 'doCountJoinAll';
    if (is_callable(array($this->getPeerClassName(), $peerCountMethod))): ?>
    $this->pager->setPeerCountMethod('<?php echo $peerCountMethod ?>');
<?php endif; ?>
    $this->pager->init();

    $result = $this->json_encode_list_pager_results($this->pager);

    $this->getResponse()->setHttpHeader("X-JSON", '()'); // set a header, although it is empty...

    return $this->renderText($result);
  }

  //JSON-data for an edit-page
  public function executeJsonEdit()
  {
    $limit = 1;
    $page = 1;

    // pager
    $this->pager = new sfPropelPager('<?php echo $this->getClassName() ?>', $limit);
    $c = new Criteria();

    <?php $groupedColumns = $this->getColumnsGrouped('edit.display'); ?>
    if ($this->getRequest()->hasParameter('key'))
    {
      $key = $this->getRequest()->getParameter('key');
      $c->add(<?php echo $this->getPeerClassName() ?>::<?php echo strtoupper($groupedColumns['pk']->getName()) ?>, $key);
    }

    $this->pager->setCriteria($c);
    $this->pager->setPage($page);

<?php $peerMethod = $this->getParameterValue('edit.peer_method') ? $this->getParameterValue('edit.peer_method') : $this->getParameterValue('peer_method') ? $this->getParameterValue('peer_method') : 'doSelectJoinAll';
    if (is_callable(array($this->getPeerClassName(), $peerMethod))): ?>
    $this->pager->setPeerMethod('<?php echo $peerMethod ?>');
<?php endif; ?>

<?php $peerCountMethod = $this->getParameterValue('edit.peer_count_method') ? $this->getParameterValue('edit.peer_count_method') : $this->getParameterValue('peer_count_method') ? $this->getParameterValue('peer_count_method') : 'doCountJoinAll';
    if (is_callable(array($this->getPeerClassName(), $peerCountMethod))): ?>
    $this->pager->setPeerCountMethod('<?php echo $peerCountMethod ?>');
<?php endif; ?>
    $this->pager->init();

    $result = $this->json_encode_edit_pager_results($this->pager);

    $this->getResponse()->setHttpHeader("X-JSON", '()');

    return $this->renderText($result);
  }

  public function executeListPrint()
  {
    $this->executeList();

    $this->print = true;

    $this->setLayout(false); // TODO: set to a print layout...
    $this->setTemplate('list');
  }

  public function executeList()
  {
    $this->processSort();

    $this->processFilters();

<?php if ($this->getParameterValue('list.filters')): ?>
    $this->filters = $this->getUser()->getAttributeHolder()->getAll('sf_admin/<?php echo $this->getSingularName() ?>/filters');
<?php endif; ?>

    // pager
    $this->pager = new sfPropelPager('<?php echo $this->getClassName() ?>', <?php echo $this->getParameterValue('list.max_per_page', 20) ?>);
    $c = new Criteria();
<?php if ($fields = $this->getParameterValue('list.fields')): ?>
<?php foreach ($fields as $key => $field): ?>
<?php if ($join_fields = $this->getParameterValue('list.fields.'.$key.'.join_fields')): ?>
    $c->addJoin(<?php echo $join_fields[0]?>,<?php echo $join_fields[1]?>);
<?php endif; ?>
<?php endforeach; ?>
<?php endif; ?>
    $this->addSortCriteria($c);
    $this->addFiltersCriteria($c);
    $this->pager->setCriteria($c);
    $this->pager->setPage($this->getRequestParameter('page', 1));
<?php if ($peerMethod = $this->getParameterValue('list.peer_method') ? $this->getParameterValue('list.peer_method') : $this->getParameterValue('peer_method') ? $this->getParameterValue('peer_method') : false): ?>
    $this->pager->setPeerMethod('<?php echo $peerMethod ?>');
<?php endif; ?>
<?php if ($peerCountMethod = $this->getParameterValue('list.peer_count_method') ? $this->getParameterValue('list.peer_count_method') : $this->getParameterValue('peer_count_method') ? $this->getParameterValue('peer_count_method') : false): ?>
    $this->pager->setPeerCountMethod('<?php echo $peerCountMethod ?>');
<?php endif; ?>
    $this->pager->init();


<?php if ($this->getParameterValue('ajax', sfConfig::get('app_sf_extjs_theme_plugin_ajax', true))): ?>
    $this->setTemplate('listAjax');
<?php endif; ?>

  }

  public function executeCreate()
  {
    return $this->forward('<?php echo $this->getModuleName() ?>', 'edit');
  }

  public function executeSave()
  {
    return $this->forward('<?php echo $this->getModuleName() ?>', 'edit');
  }

  public function executeAjaxEdit()
  {
    $this-><?php echo $this->getSingularName() ?> = $this->get<?php echo $this->getClassName() ?>OrCreate();

    // Check if retreiving instead of saving...
    if ( (!$this->getRequest()->hasParameter('cmd')) || ($this->getRequest()->getParameter('cmd')=='load') || ($this->getRequest()->hasParameter('filter')))
    {
      return $this->forward('<?php echo $this->getModuleName() ?>', 'jsonEdit');
    }

    // TODO: fix saving drop-down-comboboxes set to an empty value. (currently this gets ignored and previous value is maintained)
    if ($fieldName = $this->getRequestParameter('field'))
    {
      $value = $this->getRequestParameter('value');

      $this->update<?php echo $this->getClassName() ?>ListFromRequest($fieldName, $value);
    }
    else
    {
      $this->update<?php echo $this->getClassName() ?>FromRequest();
    }

    $this->save<?php echo $this->getClassName() ?>($this-><?php echo $this->getSingularName() ?>);

<?php
    $params = array();
    foreach ($this->getPrimaryKey() as $pk)
    {
      $params['id']  = $this->getColumnGetter($pk, true, 'this->');
    }
?>
    <?php $objectName = $this->getParameterValue('object_name', $this->getModuleName()) ?>
    sfLoader::loadHelpers('I18N');

    $<?php echo $this->getSingularName() ?> = $this-><?php echo $this->getSingularName() ?>;

    // this lets us set success to false somewhere else if needed
    $this->success = (isset($this->success))?$this->success:true;
    $this->id = <?php echo $params['id'] ?>;
    $this->title = <?php echo $this->getI18NString('edit.title', 'Edit '.$objectName, false) ?>;
    return $this->sendAjaxResponse();
  }

  public function executeAjaxMultiEdit()
  {
    $ids = json_decode($this->getRequestParameter('ids'));

    if(!is_array($ids))
    {
      $ids = array($ids);
    }

    foreach($ids as $id)
    {
      $this->getRequest()->setParameter('id', $id);
      $this-><?php echo $this->getSingularName() ?> = $this->get<?php echo $this->getClassName() ?>OrCreate();

      if ($fieldName = $this->getRequestParameter('field'))
      {
        $value = $this->getRequestParameter('value');

        $this->update<?php echo $this->getClassName() ?>ListFromRequest($fieldName, $value);
      }
      else
      {
        $this->update<?php echo $this->getClassName() ?>FromRequest();
      }

      $this->save<?php echo $this->getClassName() ?>($this-><?php echo $this->getSingularName() ?>);
    }

    $<?php echo $this->getSingularName() ?> = $this-><?php echo $this->getSingularName() ?>;

    // this lets us set success to false somewhere else if needed
    $this->success = (isset($this->success))?$this->success:true;
    $this->id = $this->getRequestParameter('ids');
    $this->title = count($ids)." issue(s) marked $value";
    return $this->sendAjaxResponse();
  }

  public function executeEdit()
  {
    $this-><?php echo $this->getSingularName() ?> = $this->get<?php echo $this->getClassName() ?>OrCreate();

    if ($this->getRequest()->getMethod() == sfRequest::POST)
    {
      $this->update<?php echo $this->getClassName() ?>FromRequest();

      $this->save<?php echo $this->getClassName() ?>($this-><?php echo $this->getSingularName() ?>);

      $this->setFlash('notice', 'Your modifications have been saved');

      if ($this->getRequestParameter('save_and_add'))
      {
        return $this->redirect('<?php echo $this->getModuleName() ?>/create');
      }
      else if ($this->getRequestParameter('save_and_list'))
      {
        return $this->redirect('<?php echo $this->getModuleName() ?>/list');
      }
      else
      {
        return $this->redirect('<?php echo $this->getModuleName() ?>/edit?<?php echo $this->getPrimaryKeyUrlParams('this->') ?>);
      }
    }
    else
    {
      $this->labels = $this->getLabels();
    }

<?php if ($this->getParameterValue('ajax', sfConfig::get('app_sf_extjs_theme_plugin_ajax', true))): ?>
    if ($this->getRequest()->isXmlHttpRequest())
    {
      $this->setLayout(false);
    }
    return 'AjaxSuccess';
<?php endif; ?>

  }

  public function executeDelete()
  {
    $this-><?php echo $this->getSingularName() ?> = <?php echo $this->getPeerClassName() ?>::retrieveByPk(<?php echo $this->getRetrieveByPkParamsForAction(40) ?>);
    $this->forward404Unless($this-><?php echo $this->getSingularName() ?>);

    try
    {
      $this->delete<?php echo $this->getClassName() ?>($this-><?php echo $this->getSingularName() ?>);
    }
    catch (PropelException $e)
    {
      $this->getRequest()->setError('delete', 'Could not delete the selected <?php echo sfInflector::humanize($this->getSingularName()) ?>. Make sure it does not have any associated items.');
      return $this->forward('<?php echo $this->getModuleName() ?>', 'list');
    }

<?php foreach ($this->getColumnCategories('edit.display') as $category): ?>
<?php foreach ($this->getColumns('edit.display', $category) as $name => $column): ?>
<?php $input_type = $this->getParameterValue('edit.fields.'.$column->getName().'.type') ?>
<?php if ($input_type == 'admin_input_file_tag'): ?>
<?php $upload_dir = $this->replaceConstants($this->getParameterValue('edit.fields.'.$column->getName().'.upload_dir')) ?>
      $currentFile = sfConfig::get('sf_upload_dir')."/<?php echo $upload_dir ?>/".$this-><?php echo $this->getSingularName() ?>->get<?php echo $column->getPhpName() ?>();
      if (is_file($currentFile))
      {
        unlink($currentFile);
      }

<?php endif; ?>
<?php endforeach; ?>
<?php endforeach; ?>
<?php $objectName = $this->getParameterValue('object_name', $this->getModuleName()) ?>
<?php if(! ($deleteNotification = $this->getParameterValue('edit.delete_notification'))) $deleteNotification = 'The '.$objectName.' has been deleted' ?>
    $this->setFlash('notice', '<?php echo $deleteNotification ?>');

    return $this->redirect('<?php echo $this->getModuleName() ?>/list');
  }

  public function executeAjaxDelete()
  {
    $id = json_decode($this->getRequestParameter('id'));

    if(!is_array($id)){
      $id = array($id);
    }

    $<?php echo $this->getSingularName() ?> = <?php echo $this->getPeerClassName() ?>::retrieveByPKs($id);
    $this->forward404Unless($<?php echo $this->getSingularName() ?>);

    foreach($<?php echo $this->getSingularName() ?> as $this-><?php echo $this->getSingularName()?>)
    {
      try
      {
        $this->delete<?php echo $this->getClassName() ?>($this-><?php echo $this->getSingularName() ?>);
      }
      catch (PropelException $e)
      {
        return $this->renderText('{"success":false,"message":"Could not delete the selected <?php echo sfInflector::humanize($this->getSingularName()) ?>. Make sure it does not have any associated items." }');
      }
    }

    $this->setLayout(false);
    return $this->renderText('{ "success": true, "message": "'.count($id).' records deleted" }');
  }

  public function handleErrorEdit()
  {
    $this->preExecute();
    $this-><?php echo $this->getSingularName() ?> = $this->get<?php echo $this->getClassName() ?>OrCreate();
    $this->update<?php echo $this->getClassName() ?>FromRequest();

    $this->labels = $this->getLabels();

<?php if (!$this->getParameterValue('ajax', sfConfig::get('app_sf_extjs_theme_plugin_ajax', true))): ?>
    return sfView::SUCCESS;
<?php else: ?>
    return 'AjaxSuccess';
<?php endif; ?>
  }

  protected function save<?php echo $this->getClassName() ?>($<?php echo $this->getSingularName() ?>)
  {
    $<?php echo $this->getSingularName() ?>->save();

<?php foreach ($this->getColumnCategories('edit.display') as $category): ?>
<?php foreach ($this->getColumns('edit.display', $category) as $name => $column): ?>
<?php $type = $column->getCreoleType() ?>
<?php $name = $column->getName() ?>
<?php if ($column->isPrimaryKey()) continue ?>
<?php $credentials = $this->getParameterValue('edit.fields.'.$column->getName().'.credentials') ?>
<?php $input_type = $this->getParameterValue('edit.fields.'.$column->getName().'.type') ?>
<?php

$user_params = $this->getParameterValue('edit.fields.'.$column->getName().'.params');
$user_params = is_array($user_params) ? $user_params : sfToolkit::stringToArray($user_params);
$through_class = isset($user_params['through_class']) ? $user_params['through_class'] : '';

?>
<?php if ($through_class): ?>
<?php

$class = $this->getClassName();
$related_class = sfPropelManyToMany::getRelatedClass($class, $through_class);
$related_table = constant($related_class.'Peer::TABLE_NAME');
$middle_table = constant($through_class.'Peer::TABLE_NAME');
$this_table = constant($class.'Peer::TABLE_NAME');

$related_column = sfPropelManyToMany::getRelatedColumn($class, $through_class);
$column = sfPropelManyToMany::getColumn($class, $through_class);

?>
<?php if ($input_type == 'admin_double_list' || $input_type == 'admin_check_list' || $input_type == 'admin_select_list'): ?>
<?php if ($credentials): $credentials = str_replace("\n", ' ', var_export($credentials, true)) ?>
    if ($this->getUser()->hasCredential(<?php echo $credentials ?>))
    {
<?php endif; ?>
      // Update many-to-many for "<?php echo $name ?>"
      $c = new Criteria();
      $c->add(<?php echo $through_class ?>Peer::<?php echo strtoupper($column->getColumnName()) ?>, $<?php echo $this->getSingularName() ?>->getPrimaryKey());
      <?php echo $through_class ?>Peer::doDelete($c);

      $ids = $this->getRequestParameter('associated_<?php echo $name ?>');
      if (is_array($ids))
      {
        foreach ($ids as $id)
        {
          $<?php echo ucfirst($through_class) ?> = new <?php echo $through_class ?>();
          $<?php echo ucfirst($through_class) ?>->set<?php echo $column->getPhpName() ?>($<?php echo $this->getSingularName() ?>->getPrimaryKey());
          $<?php echo ucfirst($through_class) ?>->set<?php echo $related_column->getPhpName() ?>($id);
          $<?php echo ucfirst($through_class) ?>->save();
        }
      }

<?php if ($credentials): ?>
    }
<?php endif; ?>
<?php endif; ?>
<?php endif; ?>
<?php endforeach; ?>
<?php endforeach; ?>
  }

  protected function delete<?php echo $this->getClassName() ?>($<?php echo $this->getSingularName() ?>)
  {
    $<?php echo $this->getSingularName() ?>->delete();
  }

  protected function update<?php echo $this->getClassName() ?>ListFromRequest($columnName, $columnValue)
  {
<?php
  $groupedColumns = $this->getColumnsGrouped('list.display');
  $columns = $this->getListUniqueColumns($groupedColumns, true);

  $tableName = $this->getTableName();
  $first = true;


  //TODO: check this out
  /*
  public static function updateField($peer, $id, $field, $value) {
    if (!class_exists($peer)) {
        throw new InvalidArgumentException($peer.' does not exist');
    }

    $method = new ReflectionMethod($peer, 'retrieveByPk');
    $object = $method->invoke(NULL, $id);

    $object->setByName($field, $value, BasePeer::TYPE_FIELDNAME);
    $object->save();
    return $object;
  }
* */


  foreach ($columns as $column):

    $columnName = $column->key;

    if ($column->isPrimaryKey()) continue;
    if (false !== strpos($columnName, '/')) continue; // TODO: at the moment cannot handle foreign fields

    $type = $column->getCreoleType();
?>
    <?php echo !$first ? 'else' : '' ?>if ($columnName == '<?php echo str_replace('/', $tableDelimiter, $column->key) ?>')
    {
<?php $credentials = $this->getParameterValue('list.fields.'.$columnName.'.credentials') ?>
<?php if ($credentials): $credentials = str_replace("\n", ' ', var_export($credentials, true)) ?>
    if ($this->getUser()->hasCredential(<?php echo $credentials ?>))
    {
<?php endif; ?>
<?php if ($type != CreoleTypes::BOOLEAN): ?>
      if (isset($columnValue)):
<?php endif; ?>
<?php if ($type == CreoleTypes::DATE || $type == CreoleTypes::TIMESTAMP): ?>
        if ($columnValue)
        {
          try
          {
            $this-><?php echo $this->getSingularName() ?>->set<?php echo $column->getPhpName() ?>($columnValue);
          }
          catch (sfException $e)
          {
            // not a date
            //TODO: do something here to send a failure notice if the date doesn't get set
          }
        }
        else
        {
          $this-><?php echo $this->getSingularName() ?>->set<?php echo $column->getPhpName() ?>(null);
        }
<?php elseif ($type == CreoleTypes::BOOLEAN): ?>
        $this-><?php echo $this->getSingularName() ?>->set<?php echo $column->getPhpName() ?>($columnValue == 'true' ? true : false);
<?php elseif ($column->isForeignKey()): ?>
        $this-><?php echo $this->getSingularName() ?>->set<?php echo $column->getPhpName() ?>($columnValue); //save foreign-key
<?php else: ?>
        $this-><?php echo $this->getSingularName() ?>->set<?php echo $column->getPhpName() ?>($columnValue);
<?php endif; ?>
<?php if ($type != CreoleTypes::BOOLEAN): ?>
      endif;
<?php endif; ?>
<?php if ($credentials): ?>
    }
<?php endif; ?>
    }

<?php $first = false; endforeach; ?>
  }

  // TODO: I suspect the credential checks to be invalid! This should be done at runtime if you ask me!
  protected function update<?php echo $this->getClassName() ?>FromRequest()
  {
    $<?php echo $this->getSingularName() ?> = $this->getRequestParameter('<?php echo $this->getTableName() ?>');

<?php
  $groupedColumns = $this->getColumnsGrouped('edit.display');
  $columns = $this->getListUniqueColumns($groupedColumns, true);
  $tableName = $this->getTableName();

  foreach ($columns as $column):
    $columnName = $column->key;

    if ($column->isPrimaryKey()) continue;
    if (false !== strpos($columnName, '/')) continue; // TODO: at the moment cannot handle foreign fields

    $type = $column->getCreoleType();
    $name = str_replace('/', $tableDelimiter, $columnName);

    $credentials = $this->getParameterValue('edit.fields.'.$columnName.'.credentials');
    $input_type = $this->getParameterValue('edit.fields.'.$columnName.'.type');

?>
<?php
  if ($credentials):
    $credentials = str_replace("\n", ' ', var_export($credentials, true))
?>
    if ($this->getUser()->hasCredential(<?php echo $credentials ?>))
    {
<?php endif; ?>
<?php if ($input_type == 'admin_input_file_tag'): ?>
<?php $upload_dir = $this->replaceConstants($this->getParameterValue('edit.fields.'.$columnName.'.upload_dir')) ?>
    $currentFile = sfConfig::get('sf_upload_dir')."/<?php echo $upload_dir ?>/".$this-><?php echo $this->getSingularName() ?>->get<?php echo $column->getPhpName() ?>();
    if (!$this->getRequest()->hasErrors() && isset($<?php echo $this->getSingularName() ?>['<?php echo $name ?>_remove']))
    {
      $this-><?php echo $this->getSingularName() ?>->set<?php echo $column->getPhpName() ?>('');
      if (is_file($currentFile))
      {
        unlink($currentFile);
      }
    }

    if (!$this->getRequest()->hasErrors() && $this->getRequest()->getFileSize('<?php echo $this->getSingularName() ?>[<?php echo $name ?>]'))
    {
<?php elseif ($type != CreoleTypes::BOOLEAN): ?>
    if (isset($<?php echo $this->getSingularName() ?>['<?php echo $name ?>'])):
<?php endif; ?>
<?php if ($input_type == 'admin_input_file_tag'): ?>
<?php if ($this->getParameterValue('edit.fields.'.$columnName.'.filename')): ?>
      $fileName = "<?php echo str_replace('"', '\\"', $this->replaceConstants($this->getParameterValue('edit.fields.'.$columnName.'.filename'))) ?>";
<?php else: ?>
      $fileName = md5($this->getRequest()->getFileName('<?php echo $this->getSingularName() ?>[<?php echo $name ?>]').time().rand(0, 99999));
<?php endif; ?>
      $ext = $this->getRequest()->getFileExtension('<?php echo $this->getSingularName() ?>[<?php echo $name ?>]');
      if (is_file($currentFile))
      {
        unlink($currentFile);
      }
      $this->getRequest()->moveFile('<?php echo $this->getSingularName() ?>[<?php echo $name ?>]', sfConfig::get('sf_upload_dir')."/<?php echo $upload_dir ?>/".$fileName.$ext);
      $this-><?php echo $this->getSingularName() ?>->set<?php echo $column->getPhpName() ?>($fileName.$ext);
<?php elseif ($type == CreoleTypes::DATE || $type == CreoleTypes::TIMESTAMP): ?>
      if ($<?php echo $this->getSingularName() ?>['<?php echo $name ?>'])
      {
        try
        {
          $dateFormat = new sfDateFormat($this->getUser()->getCulture());
          <?php $inputPattern  = $type == CreoleTypes::DATE ? 'd' : 'g'; ?>
          <?php $outputPattern = $type == CreoleTypes::DATE ? 'i' : 'I'; ?>
          if (!is_array($<?php echo $this->getSingularName() ?>['<?php echo $name ?>']))
          {
            $value = $dateFormat->format($<?php echo $this->getSingularName() ?>['<?php echo $name ?>'], '<?php echo $outputPattern ?>', $dateFormat->getInputPattern('<?php echo $inputPattern ?>'));
          }
          else
          {
            $value_array = $<?php echo $this->getSingularName() ?>['<?php echo $name ?>'];
            $value = $value_array['year'].'-'.$value_array['month'].'-'.$value_array['day'].(isset($value_array['hour']) ? ' '.$value_array['hour'].':'.$value_array['minute'].(isset($value_array['second']) ? ':'.$value_array['second'] : '') : '');
          }
          $this-><?php echo $this->getSingularName() ?>->set<?php echo $column->getPhpName() ?>($value);
        }
        catch (sfException $e)
        {
          // not a date
        }
      }
      else
      {
        $this-><?php echo $this->getSingularName() ?>->set<?php echo $column->getPhpName() ?>(null);
      }
<?php elseif ($type == CreoleTypes::BOOLEAN): ?>
    $this-><?php echo $this->getSingularName() ?>->set<?php echo $column->getPhpName() ?>(isset($<?php echo $this->getSingularName() ?>['<?php echo $name ?>']) ? ($<?php echo $this->getSingularName() ?>['<?php echo $name ?>'] == 'true' ? true : false) : false);
<?php elseif ($column->isForeignKey()): ?>
//        $this-><?php echo $this->getSingularName() ?>->set<?php echo $column->getPhpName() ?>($<?php echo $this->getSingularName() ?>['<?php echo $name ?>'] ? $<?php echo $this->getSingularName() ?>['<?php echo $name ?>'] : null);
        if ($<?php echo $this->getSingularName() ?>['<?php echo $name ?>'] != null)
        {
          $this-><?php echo $this->getSingularName() ?>->set<?php echo $column->getPhpName() ?>($<?php echo $this->getSingularName() ?>['<?php echo $name ?>']);
        }
        else
        {
          // TODO: maybe remove this value from the database
        }
<?php else: ?>
        $this-><?php echo $this->getSingularName() ?>->set<?php echo $column->getPhpName() ?>($<?php echo $this->getSingularName() ?>['<?php echo $name ?>']);
<?php endif; ?>
<?php if ($type != CreoleTypes::BOOLEAN): ?>
    endif;
<?php endif; ?>
<?php if ($credentials): ?>
      }
<?php endif; ?>
<?php endforeach; ?>
  }

  protected function get<?php echo $this->getClassName() ?>OrCreate(<?php echo $this->getMethodParamsForGetOrCreate() ?>)
  {
    if (<?php echo $this->getTestPksForGetOrCreate() ?>)
    {
      $<?php echo $this->getSingularName() ?> = new <?php echo $this->getClassName() ?>();
    }
    else
    {
      $<?php echo $this->getSingularName() ?> = <?php echo $this->getPeerClassName() ?>::retrieveByPk(<?php echo $this->getRetrieveByPkParamsForGetOrCreate() ?>);

      $this->forward404Unless($<?php echo $this->getSingularName() ?>);
    }

    return $<?php echo $this->getSingularName() ?>;
  }

  protected function processFilters($namespace = "<?php echo $this->getSingularName() ?>")
  {
    if ($this->getRequest()->hasParameter('filter'))
    {
      $filters = $this->getRequestParameter('filters');

<?php if ($this->getParameterValue('list.filters')): ?>
<?php
    //foreach ($this->getColumns('list.filters') as $column)
    //{
      //$type = $column->getCreoleType();

      //$last = strrpos($column->key, '/');
      //$cname = substr($column->key, $last + 1);
//if ($type == CreoleTypes::DATE || $type == CreoleTypes::TIMESTAMP): //TODO replace $column->getName with $columnName
?>
//      if (isset($filters['<?php //echo $cname ?>']['from']) && $filters['<?php //echo $cname ?>']['from'] !== '')
//      {
//        $filters['<?php //echo $cname ?>']['from'] = sfI18N::getTimestampForCulture($filters['<?php //echo $cname ?>']['from'], $this->getUser()->getCulture());
//      }
//      if (isset($filters['<?php //echo $cname ?>']['to']) && $filters['<?php //echo $cname ?>']['to'] !== '')
//      {
//        $filters['<?php //echo $cname ?>']['to'] = sfI18N::getTimestampForCulture($filters['<?php //echo $cname ?>']['to'], $this->getUser()->getCulture());
//      }
<?php //endif;
    //}
    ?>
<?php endif; ?>

      /* reset Multi-sort // TODO, this should be done somewhere else,
        PROPOSAL: have two different methodes to sort,
        one for sort-on-one-column, one for multi-sort
        one sort calls this reset + multisort() to sort on one column...
        use javascript to test is shift is pressed,
        - if shift not pressed: sort on one column, if shift pressed: sort on multiple-columns
      */
      if (is_array($filters))
      {
        $this->getUser()->getAttributeHolder()->removeNamespace('sf_admin/'.$namespace.'/filters');
        $this->getUser()->getAttributeHolder()->add($filters, 'sf_admin/'.$namespace.'/filters');
      }
    }
    else
    {
      $this->getUser()->getAttributeHolder()->removeNamespace('sf_admin/'.$namespace.'/filters');
    }
  }

  protected function processSort($type = null, $namespace = "<?php echo $this->getSingularName() ?>")
  {
      /* reset Multi-sort // TODO, Now done ine processFilters
        PROPOSAL: have two different methodes to sort,
        one for sort-on-one-column, one for multi-sort
        one sort calls this reset + multisort() to sort on one column...
        use javascript to test is shift is pressed,
        - if shift not pressed: sort on one column, if shift pressed: sort on multiple-columns
      */

<?php $multisort = $this->getParameterValue('list.multisort'); ?>

    // process sort action
    $sort = $this->getRequestParameter('sort');
    if ($type == null ) $type = $this->getRequestParameter('type');

    if ($sort)
    {
<?php if (!$multisort) :?>
      // if not multisort, first reset sort-order before setting new sort-order
      $this->getUser()->getAttributeHolder()->removeNamespace("sf_admin/$namespace/sort");
<?php endif; ?>

      $this->getUser()->setAttribute($sort, $type, "sf_admin/$namespace/sort");
    }

<?php if ($sort = $this->getParameterValue('list.sort')): ?>
    // If not yet sorting, sort as specified in generator.yml (if specified)
    if (!$this->getUser()->getAttributeHolder()->getAll("sf_admin/$namespace/sort"))
    {
<?php if (is_array($sort)): //multiple sort columns ?>

<?php if (!$multisort) :?>
      $this->getUser()->setAttribute('<?php echo str_replace('/', $tableDelimiter, $sort[0]) ?>', '<?php echo $sort[1] ?>', "sf_admin/$namespace/sort");
<?php else: // if multisort ?>
<?php foreach ($sort as $s) : ?>
<?php if (is_array($s)): // if [column, direction] ?>
      $this->getUser()->setAttribute('<?php echo str_replace('/', $tableDelimiter, $s[0]) ?>', '<?php echo $s[1] ?>', "sf_admin/$namespace/sort");
<?php else: // if sort-column is not an array: only sort column ?>
      $this->getUser()->setAttribute('<?php echo str_replace('/', $tableDelimiter, $s) ?>', 'asc', "sf_admin/$namespace/sort");
<?php endif; ?>
<?php endforeach; ?>
<?php endif; //end multisort ?>

<?php else: // if only one sort column ?>
      $this->getUser()->setAttribute('<?php echo str_replace('/', $tableDelimiter, $sort) ?>', 'asc', "sf_admin/$namespace/sort");
<?php endif; //end columns array test ?>
    }
<?php endif; // endif list.sort parameter ?>
  }

  protected function addGroupCriteria($c)
  {
<?php
  $for = array('list.filters', 'list.display', 'edit.display');
  $groupedColumns = $this->getColumnsGrouped($for, false);

  $pk = clone($groupedColumns['pk']);
  $pk->key = strtolower($pk->getName());

  $columns = array();
  $columns[] = $pk; // add primary key
  // add primary keys of related classes
  foreach ($groupedColumns['related'] as $foreignKey => $relatedGroupedColumns)
  {
    $pkr = clone($relatedGroupedColumns['pk']);
    $pkr->key = strtolower($pkr->getTableName().'/'.$pkr->getName());

    $columns[] = $pkr; // add related primary key
  }
  // add default keys below PKs
  $columns = array_merge($columns, $this->getListUniqueColumns($groupedColumns, false));

?>
<?php foreach ($columns as $column):
    $last = strrpos($column->key, '/');
    $cname = $column->key;
    if ($last>0) $cname = substr($cname, $last + 1);
?>
<?php $columnName = strtoupper($cname); ?>
<?php if (($column->key == '*') || ($column->key == '^expander') || ($column->key == '^rowactions') || ($cname=='')) continue ?>
<?php $type = $column->getCreoleType() ?>
<?php
  $peerClassName = $this->getPeerClassName();
  if ((false !== strpos($column->key, '/')) )
  {
    //TODO get TablePhpName with help of groupedColumns hierarchy and part of $column->key till last /
    $peerClassName = sfInflector::camelize($column->getTable()->getPhpName()).'Peer';
  }

  // Hack to eliminate misspelled camelize classes (like sfGuard...)
  if(strpos($peerClassName, 'Sf') === 0)
  {
    $peerClassName = str_replace('Sf', 'sf', $peerClassName);
  }

?>
<?php if (($column->isPartial() || $column->isComponent()) && $this->getParameterValue('list.fields.'.$column->getName().'.filter_criteria_disabled')) continue; ?>
<?php if (!$column->isPrimaryKey()): ?>
    if (isset($this->groupby['<?php echo str_replace('/', $tableDelimiter, $column->key) ?>']))
    {
      $c->addSelectColumn(<?php echo $peerClassName ?>::<?php echo $columnName ?>);
      $c->addGroupByColumn(<?php echo $peerClassName ?>::<?php echo $columnName ?>);
    }
<?php endif; ?>
<?php endforeach; ?>
  }

  protected function addFiltersCriteria($c, $namespace = '<?php echo $this->getSingularName() ?>')
  {
<?php
  // filtering is also used for drop-down combo-box filtering and retreiving json-data for edit-pages!
  $for = array('list.filters', 'list.display', 'edit.display');
  $groupedColumns = $this->getColumnsGrouped($for, false);

  $pk = clone($groupedColumns['pk']);
  $pk->key = strtolower($pk->getName());

  $columns = array();
  $columns[] = $pk; // add primary key
  // add primary keys of related classes
  foreach ($groupedColumns['related'] as $foreignKey => $relatedGroupedColumns)
  {
    $pkr = clone($relatedGroupedColumns['pk']);
    $pkr->key = strtolower($pkr->getTableName().'/'.$pkr->getName());

    $columns[] = $pkr; // add related primary key
  }
  // add default keys below PKs
  $columns = array_merge($columns, $this->getListUniqueColumns($groupedColumns, false));

?>
<?php foreach ($columns as $column):
    $last = strrpos($column->key, '/');
    $cname = $column->key;
    if ($last>0) $cname = substr($cname, $last + 1);
?>
<?php $columnName = strtoupper($cname); ?>
<?php if (($column->key == '*') || ($column->key == '^expander') || ($column->key == '^rowactions') ||($cname=='')) continue ?>
<?php $type = $column->getCreoleType() ?>
<?php
  $peerClassName = $this->getPeerClassName();
  if ((false !== strpos($column->key, '/')) )
  {
    //TODO get TablePhpName with help of groupedColumns hierarchy and part of $column->key till last /
    $peerClassName = sfInflector::camelize($column->getTable()->getPhpName()).'Peer';
  }

  // Hack to eliminate misspelled camelize classes (like sfGuard...)
  if(strpos($peerClassName, 'Sf') === 0)
  {
    $peerClassName = str_replace('Sf', 'sf', $peerClassName);
  }

?>
<?php if (($column->isPartial() || $column->isComponent()) && $this->getParameterValue('list.fields.'.$column->getName().'.filter_criteria_disabled')) continue; ?>
<?php if ($type == CreoleTypes::DATE || $type == CreoleTypes::TIMESTAMP): ?>
    if (isset($this->filters['<?php echo str_replace('/', $tableDelimiter, $column->key) ?>']) && $this->filters['<?php echo str_replace('/', $tableDelimiter, $column->key) ?>'] !== '')
    {
      $dateStart = strtotime($this->filters['<?php echo str_replace('/', $tableDelimiter, $column->key) ?>']);
      $dateEnd = mktime(0, 0, 0, date("m",$dateStart)  , date("d",$dateStart)+1, date("Y",$dateStart));

      $criterion = $c->getNewCriterion(<?php echo $peerClassName ?>::<?php echo $columnName ?>, date('Y-m-d', $dateStart), Criteria::GREATER_EQUAL);

      $criterion->addAnd($c->getNewCriterion(<?php echo $peerClassName ?>::<?php echo $columnName ?>, date('Y-m-d', $dateEnd), Criteria::LESS_THAN));

      if (isset($criterion))
      {
        $c->add($criterion);
      }
    }
<?php else: ?>
    if (isset($this->filters['<?php echo str_replace('/', $tableDelimiter, $column->key) ?>']) && $this->filters['<?php echo str_replace('/', $tableDelimiter, $column->key) ?>'] !== '')
    {
<?php if ($type == CreoleTypes::CHAR || $type == CreoleTypes::VARCHAR || $type == CreoleTypes::LONGVARCHAR): ?>
      $q = '';
      if ($this->getRequest()->getParameter('filter') == 'query') $q = '%';
      $c->add(<?php echo $peerClassName ?>::<?php echo $columnName ?>, strtr($this->filters['<?php echo str_replace('/', $tableDelimiter, $column->key) ?>'].$q, '*', '%'), Criteria::LIKE);
<?php elseif ($type == CreoleTypes::BOOLEAN): ?>
      $bool = ($this->filters['<?php echo str_replace('/', $tableDelimiter, $column->key) ?>']=='true')?true:false;
      if($bool) $c->add(<?php echo $peerClassName ?>::<?php echo $columnName ?>, $bool);
<?php else: ?>
      $c->add(<?php echo $peerClassName ?>::<?php echo $columnName ?>, $this->filters['<?php echo str_replace('/', $tableDelimiter, $column->key) ?>']);
<?php endif; ?>
    }
<?php endif; ?>
<?php endforeach; ?>
  }

  protected function addSortCriteria($c, $namespace = "<?php echo $this->getSingularName() ?>")
  {
    $sort_array = $this->getUser()->getAttributeHolder()->getAll("sf_admin/$namespace/sort");
    $tableDelimiter = sfConfig::get('app_sf_extjs_theme_plugin_table_delimiter', '-');

    if ($sort_array) // if sort-columns are set
    {
      $sort_columns = array();
      foreach($sort_array as $sort_column => $sort_type)
      {
        $fields = explode($tableDelimiter, $sort_column);

        //get className
        $className = '<?php echo $this->getClassName() ?>';
        for ($i = 0; $i < count($fields) -1; $i++)
        {
          $map = call_user_func(array($className.'Peer', 'getTableMap'));
          $columns = $map->getColumns();
          $found = false;
          foreach ($columns as $column)
          {
            if ($column->getColumnName() == strtoupper($fields[$i]))
            {
              $found = true;
              break;
            }
          }

          if (!$found)
          {
            throw new Exception('Sort column "'.$sort_column.'" not found');
          }

          $className = $this->translateTableNameToPhpName($column->getRelatedTableName());
        }

        //get sortColumn
        $map = call_user_func(array($className.'Peer', 'getTableMap'));
        $columns = $map->getColumns();
        $found = false;
        foreach ($columns as $column)
        {
          if ($column->getColumnName() == strtoupper($fields[$i]))
          {
            $found = true;
            break;
          }
        }

        try
        {
          if ($found)
          {
            $fieldName = call_user_func(array($className.'Peer', 'translateFieldName'), $column->getPhpName(), BasePeer::TYPE_PHPNAME, BasePeer::TYPE_COLNAME);
          }
          // Because of this the sort-exception below will probably never fire anymore...
          // This else-clause is there to make it possible to sort on custom collumns ( $c->addAsColumn(...) )
          else
          {
            $fieldName = str_replace('-', '_', $sort_column);
          }
          $sort_columns[$fieldName] = $sort_type;
        }
        catch (Exception $e)
        {
          // sort column does not exist, throw error
          throw new sfException(sprintf('Cannot sort on column "%s", the column does not exist. Clearing your cookies (to remove the session-key) will (temporarily) fix this error. If this happens often and you can reproduce, please inform me at the forum!', $sortColumn));
        }

        if ($sort_type=='none')
        {
          $this->getUser()->getAttributeHolder()->remove($sort_column,"sf_admin/$namespace/sort");
        }
      }

      foreach($sort_columns as $sort_column => $sort_type)
      {
        switch ($sort_type)
        {
          case 'asc':
            $c->addAscendingOrderByColumn($sort_column);
            break;
          case 'desc':
            $c->addDescendingOrderByColumn($sort_column);
            break;
        }
      }
    }

  }

  protected function getLabels()
  {
    return array(
<?php
  $groupedColumns = $this->getColumnsGrouped('edit.display');
  $columns = $this->getListUniqueColumns($groupedColumns, true);
  $tableName = $this->getTableName();

  foreach ($columns as $columnName => $column) :
    $columnName = $column->key;

    $fieldName = str_replace('/', $tableDelimiter, $columnName);

    $labelName = str_replace("'", "\\'", $this->getParameterValue('edit.fields.'.$columnName.'.name'));
?>
      '<?php echo $fieldName ?>' => '<?php echo $labelName ?>',
<?php endforeach; ?>
    );
  }


  protected function translateTableNameToPhpName($tableName)
  {
    switch($tableName)
    {
<?php
  $relatedColumns = $this->getListRelatedGroupedColumns($this->getColumnsGrouped(array('list.display', 'edit.display')));
  foreach ($relatedColumns as $foreignKey => $relatedTable):
?>
      case '<?php echo $relatedTable['pk']->getTable()->getName(); ?>':
        return '<?php echo $relatedTable['pk']->getTable()->getPhpName(); ?>';
<?php endforeach; ?>
    }

    throw new Exception('Unknown table name: "'.$tableName.'"');
  }

  public function executeListAjaxJs()
  {

  }

  public function executeListAjaxGridPanelJs()
  {

  }

  public function executeListAjaxFilterPanelJs()
  {

  }

  public function executeListAjaxTabPanelJs()
  {

  }

  public function executeListAjaxRowActionsJs()
  {

  }

  public function executeRelatedAjaxEditors()
  {
    $this->setTemplate('relatedAjaxEditors');
  }

  public function executeEditAjaxJs()
  {

  }

  public function executeAjaxUploadReceive()
  {
    if ($this->getRequest()->hasFiles())
    {
      $fileRealName = '';
      $fileError = false;

      foreach ($this->getRequest()->getFileNames() as $fileName)
      {
        $fileError = $this->getRequest()->hasFileError($fileName);
        if ($fileError) break;

        $fileRealName = $this->getRequest()->getFileName($fileName);

        $fileSize  = $this->getRequest()->getFileSize($fileName);
        $fileType  = $this->getRequest()->getFileType($fileName);

        $uploadDir = sfConfig::get('sf_upload_dir').sfConfig::get('app_gallery_picture_folder').DIRECTORY_SEPARATOR.$fileRealName;
        //$uploadDir = sfConfig::get('sf_upload_dir');

        $this->getRequest()->moveFile($fileName, $uploadDir);

        $this->logMessage($this->getRequest()->getFileName($fileName).' uploaded','info');
      }

      if ($fileError)
      {
        $result = "{'success':false,'message':'Upload failed, did you provide a valid filename?'}";
      }
      else
      {
        $result = "{'success':true,'message':'Upload of ".$fileRealName." was sucessfull'}";
      }
    }
    else
    {
      $result = "{'success':false,'message':'Upload failed'}";
    }

    return $this->renderText($result);
  }

/**
 *  JSON-Encoding
 */

<?php foreach(array('list', 'edit', 'autocomplete') as $for): ?>
  public function json_encode_<?php echo $for ?>_pager_results($pager)
  {
    $json = array();

    $json['totalCount'] = $pager->getNbResults();
    $json['data'] = array();

    foreach ($pager->getResults() as $<?php echo $this->getSingularName() ?>)
    {
      $json['data'][] = $this->page_to_<?php echo $for ?>_array($<?php echo $this->getSingularName() ?>);
    }

<?php if ($for == 'edit'): ?>
<?php $objectName = $this->getParameterValue('object_name', $this->getModuleName()) ?>
    sfLoader::loadHelpers('I18N');
    $json['title'] = <?php echo $this->getI18NString('edit.title', 'Edit '.$objectName, false) ?>;

<?php endif; ?>
    return json_encode($json);
  }

  protected function page_to_<?php echo $for ?>_array($<?php echo $this->getSingularName() ?>)
  {
    sfLoader::loadHelpers('Date');
    $row = array();
<?php
    $hs = $this->getParameterValue($for.'.hide', array());

    // iterate through all (related) columns of all classes
    $for_display = array();
    switch ($for)
    {
      case 'autocomplete':
        $for_display[] = 'list.filters';
      case 'list':
        $for_display[] = 'list.display';
        if ($for != 'autocomplete') break;
      case 'edit':
        $display = $this->getParameterValue('edit.display');
        $pages = $this->getParameterValue('edit.pages');

        // iterate through all (related) columns of all classes
        if ((!isset($pages) && !isset($display)) || isset($display))
        {
          $for_display[] = 'edit.display'; //TODO: this should probably return all columns instead to remain compatible with original generator
        }

        if (isset($pages))
        {
          //add all edit.pages
          //TODO: add recursion for edit.pages.pagename.pages...etc
          foreach($pages as $pageName => $page)
          {
            $for_display[] = 'edit.pages.'.$pageName.'.display';
          }
        }
    }

    $groupedColumns = $this->getColumnsGrouped($for_display);
    $columns = $this->getListUniqueColumns($groupedColumns, true);

    foreach ($columns as $columnName => $column) :
      if ($column->key == '*' || $column->key == '^expander' || $column->key == '^rowactions') continue;
      if (in_array($column->key, $hs)) continue;
      if ($column->isPartial()) continue; //partials will not end up in json-data

      if (($for == 'autocomplete') && (false === strpos($column->key, '/'))) continue; // skip local fields for autocompletion

      $credentials = $this->getParameterValue($for.'.fields.'.$column->key.'.credentials'); //TODO this key does not exists for 'autocomplete'
      if ($credentials):
        $credentials = str_replace("\n", ' ', var_export($credentials, true));
?>
    if ($this->getUser()->hasCredential(<?php echo $credentials ?>))<?php endif;
      if ($for != 'autocomplete'): ?>
    $row['<?php echo str_replace('/', $this->tableDelimiter, $column->key)  ?>'] = <?php echo $this->getColumnListTag($column) ?>;
<?php else: // if autocompleting
        list($relatedTableName) = explode('/', $column->key, 2); //$relatedColumnName
        $relatedTableName = $column->getTable()->getName();
?>
      if (strtolower($this->getRequestParameter('class')) == "<?php echo strtolower($relatedTableName) ?>")
      {
<?php
  $last                 = strrpos($column->key, '/');
  $relatedTableFKs      = substr($column->key, 0, $last);
  $relatedTableGrouped  = $this->getSubGroupedColumns($relatedTableFKs, $groupedColumns);
  $relatedTablePK       = $relatedTableGrouped['pk'];

  $columnname = substr($column->key, $last + 1);
?>
      $row['<?php echo $relatedTableName.$this->tableDelimiter.$relatedTablePK->getName() ?>'] = $<?php echo $this->getSingularName() ?>->get<?php echo sfInflector::camelize($relatedTablePK->getName()) ?>();
      $row['<?php echo $relatedTableName.$this->tableDelimiter.$columnname ?>'] = $<?php echo $this->getSingularName() ?>->get<?php echo sfInflector::camelize($columnname) ?>();
    }
<?php endif; ?>
<?php $hs[] = $column->key; //don't add it twice ?>
<?php endforeach; ?>
    return $row;
  }
<?php endforeach; ?>
}
