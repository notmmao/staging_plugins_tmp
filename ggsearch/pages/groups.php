<form action="" method="POST">
<?php
// Bereite Tabelle vor
$ggListTable = new GG_List_Table();
$ggListTable->prepare_items(); 

if (isset($_POST["submit"])) {
    $items = $ggListTable->getItems();
    
    $hidden = array();
    $prioritys = array();
    if (is_array($_POST["active"]) && is_array($_POST["priority"])) {
        foreach ($items as $value) {
            // Suche nun die versteckten Kategorien
            if (!in_array($value["uid"], $_POST["active"])) {
                $hidden[] = $value["uid"];
            }
            
            // Suche nach veränderten Prioritys
            if ($_POST["priority"][$value["uid"]] != $value["priority"]) {
                $prioritys[$value["uid"]] = $_POST["priority"][$value["uid"]];
            }
            
        }
    }
    
    GG_Core::getInstance()->getCfgGroups()->prioritys = $prioritys;
    GG_Core::getInstance()->getCfgGroups()->hidden = $hidden;
    GG_Core::getInstance()->getCfgGroups()->save();
    $ggListTable->prepare_items(); 
    
}else if (isset($_GET["reset"])) {
    // Zurücksetzen
    GG_Core::getInstance()->initConfig(true, array("groups"));
    $ggListTable->prepare_items(); 
    
}

/*
echo '
<div class="notice notice-warning">
    <p><strong>Beachte: </strong>Die Gruppen werden nach Priorität sortiert. Klicken und bewegen Sie mit der Maus die Zeilen um die Gruppen anzuordnen. Entfernen Sie das Häckchen bei den Gruppen, die nicht in die Suche mitaufgenommen werden sollen.</p>
</div>
';
*/
?>
<div class="notice notice-warning">
    <p>
        <strong><?php _e("Note", "ggsearch"); ?>: </strong>
        <?php _e("Groups are sorted by priority.", "ggsearch"); ?> 
        <?php _e("Click and drag with the mouse the line to reorder the groups.", "ggsearch"); ?> 
        <?php _e("Remove the check mark in the line, which should not be incorporated in the search.", "ggsearch"); ?>
    </p>
</div>
<?php

// Ausgeben der Tabelle
$ggListTable->display();
?>

<p class="submit">
    <a href="?page=gg-search.php&tab=groups&reset" class="button"><?php _e("Reset to Defaults", "ggsearch"); ?></a>
    <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save'); ?>">
</p>
</form>

<script>
    jQuery(document).ready(function($) {
        var el = document.getElementById('the-list'),
            jEL = $(el);
        var sortable = Sortable.create(el, {
            ghostClass: "gg-search-ghost",
            onEnd: function (evt) {
                var priority_new = jEL.children("tr").eq(evt.newIndex).find('input[type="text"]'),
                    priority_new_val = priority_new.val(),
                    prev = jEL.children("tr").eq(evt.newIndex - 1).find('input[type="text"]');
                
                if (isDefined(prev)) {
                    var prev_val = (evt.newIndex !== 0) ? parseInt(prev.val()) + 1 : 1;
                    if ((prev_val - 1) != priority_new_val) {
                        priority_new.val(prev_val);
                    }else{
                        alert("Verschieben nur in unterschiedlichen Prioritäten!");
                    }
                }
            }
        });
    });
</script>