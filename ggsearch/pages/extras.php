<form action="" method="POST">
<?php
$ggListTable = new GG_Extra_Table();
$ggListTable->prepare_items(); 

if (isset($_POST["submit"])) {
    $items = $ggListTable->getItems();
    
    //GG_Core::print_r($_POST);
    
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
    
    GG_Core::getInstance()->getCfgExtras()->prioritys = $prioritys;
    GG_Core::getInstance()->getCfgExtras()->hidden = $hidden;
    GG_Core::getInstance()->getCfgExtras()->save();
    $ggListTable->prepare_items(); 
    
}else if (isset($_GET["reset"])) {
    // Zurücksetzen
    GG_Core::getInstance()->initConfig(true, array("extras"));
    $ggListTable->prepare_items(); 
    
}

/*
echo '
<div class="notice notice-warning" style="margin: 0px 15px 15px 15px;">
    <p>Benutzerdefinierte Links beschreiben einen schnellen Link zum Ausführen bestimmter Aktionen. Dabei wird ein Link mit dem Suchbegriff generiert. So können beispielsweise schnell und unkompliziert Beiträge von der Suche aus erstellt werden.
    <code>{0}</code> im Titel und Link wird mit dem Suchbegriff ersetzt.</p>
    <p><strong>Beachte: </strong>Die Benutzerdefinierten Links werden nach Priorität sortiert. Klicken und bewegen Sie mit der Maus die Zeilen um die Gruppen anzuordnen. Entfernen Sie das Häckchen bei den Links, die nicht in die Suche mitaufgenommen werden sollen.</p>
</div>
';
*/
?>
<div class="notice notice-warning">
    <p>
        <?php _e("Extras describe a quick link to perform specific actions.", "ggsearch"); ?> 
        <?php _e("A link is generated with the search term.", "ggsearch"); ?> 
        <?php _e("So you can create, for example, quick and easy posts from search term.", "ggsearch"); ?> 
        <?php _e("<code>{0}</code> in the title and the link will be replaced with the search term.", "ggsearch"); ?>
    </p>
    <p>
        <strong><?php _e("Note", "ggsearch"); ?>: </strong>
        <?php _e("Extras are sorted by priority.", "ggsearch"); ?> 
        <?php _e("Click and drag with the mouse the line to reorder the groups.", "ggsearch"); ?> 
        <?php _e("Remove the check mark in the line, which should not be incorporated in the search.", "ggsearch"); ?>
    </p>
</div>
<?php

// Ausgeben der Tabelle
$ggListTable->display();
?>

<p class="submit">
    <a href="?page=gg-search.php&tab=extras&reset" class="button"><?php _e("Reset to Defaults", "ggsearch"); ?></a>
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