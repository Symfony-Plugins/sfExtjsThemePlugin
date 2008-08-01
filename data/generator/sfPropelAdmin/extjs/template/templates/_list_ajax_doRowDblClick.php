<?php
// get first primary key of class
$pkn = $this->getPrimaryKeyAdminColumn()->getName();

// get action
$action = $this->getParameterValue('list.object_dblclick');
?>

//        e.stopEvent();
//        var coords = e.getXY();
//        var rowRecord = gridPanel.getStore().getAt(rowIndex);

//        window.location.href = '<?php echo $this->controller->genUrl($this->getModuleName().'/'.$action)."?".$pkn."=' + rowRecord['".$pkn."']" ?>;
        Ext.Msg.alert('Double Click', 'Row '+rowIndex+' was double clicked');
