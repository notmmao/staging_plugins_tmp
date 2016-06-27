<form action="" method="POST">
<?php
$general = GG_Core::getInstance()->getCfgGeneral();
//GG_Core::print_r($general->roles);
if (isset($_POST["submit"])) {
    $general->placeholder = $_POST["placeholder"];
    $general->point = $_POST["allowpoint"] == "1";
    $general->maxlimit = $_POST["maxlimit"];
    $general->adminmenu = $_POST["adminmenusearch"] == "1";
    $general->frontend = $_POST["frontendsearch"] == "1";
    $general->roles = $_POST["roles"];
    $general->save();
}else if (isset($_GET["reset"])) {
    // Zurücksetzen
    GG_Core::getInstance()->initConfig(true, array("general"));
}
?>
<table class="form-table">
    <tbody>
        <tr>
            <th scope="row"><label for="placeholder"><?php _e("Placeholder", "ggsearch"); ?></label></th>
            <td><input name="placeholder" type="text" value="<?php echo $general->placeholder; ?>" id="placeholder" placeholder="<?php _e("Search", "ggsearch"); ?>..." class="regular-text"></td>
        </tr>
        <tr>
            <th scope="row"><code>[.]</code>-<?php _e("Extension", "ggsearch"); ?></th>
            <td>
                <fieldset>
                    <legend class="screen-reader-text"><span><code>[.]</code>-<?php _e("Extension", "ggsearch"); ?></span></legend>
                    <label for="allowpoint">
                        <input name="allowpoint" type="checkbox" id="allowpoint" <?php if ($general->point) echo 'checked="checked"'; ?> value="1"> <?php _e("Enabled", "ggsearch"); ?>
                    </label>
                </fieldset>
                <p class="description" id="tagline-description">
                    <!-- Erlaubt zusätzlich beim Klick der Taste <code>[.]</code> (Punkt) das Öffnen der Suche. !-->
                    <?php _e("Allows when clicking the button <code>.</code> (Point) to open the search.", "ggsearch"); ?>
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="maxlimit"><?php _e("Limit of Entries", "ggsearch"); ?></label></th>
            <td>
                <input name="maxlimit" type="number" id="maxlimit" value="<?php echo $general->maxlimit; ?>" class="regular-number">
                <p class="description" id="tagline-description">
                    <!-- Begrenzt die maximale Anzahl an Sucheinträgen pro Gruppe, wenn diese nicht definiert ist. !-->
                    <?php _e("Limits the maximum number of search entries per group if it is not defined.", "ggsearch"); ?>
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php _e("Search in the left admin menu", "ggsearch"); ?></th>
            <td>
                <fieldset>
                    <legend class="screen-reader-text"><span><?php _e("Search in the left admin menu", "ggsearch"); ?></span></legend>
                    <label for="adminmenusearch">
                        <input name="adminmenusearch" type="checkbox" id="adminmenusearch" <?php if ($general->adminmenu) echo 'checked="checked"'; ?> value="1"> <?php _e("Enabled", "ggsearch"); ?>
                    </label>
                </fieldset>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php _e("Search in frontend", "ggsearch"); ?></th>
            <td>
                <fieldset>
                    <legend class="screen-reader-text"><span><?php _e("Search in frontend", "ggsearch"); ?></span></legend>
                    <label for="frontendsearch">
                        <input name="frontendsearch" type="checkbox" id="frontendsearch" <?php if ($general->frontend) echo 'checked="checked"'; ?> value="1"> <?php _e("Enabled", "ggsearch"); ?>
                    </label>
                </fieldset>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php _e("Enabled for", "ggsearch"); ?></th>
            <td>
                <fieldset>
                    <legend class="screen-reader-text"><span><?php _e("Enabled for", "ggsearch"); ?></span></legend>
                    <?php foreach (get_editable_roles() as $role_name => $role_info) {
                        echo '<input name="roles[]" type="checkbox" ' . ((in_array($role_name, $general->roles)) ? 'checked="checked"' : "") . ' value="' . $role_name . '"> ' . $role_info["name"] . '<br/>';
                    } ?>
                </fieldset>
            </td>
        </tr>
    </tbody>
</table>
<p class="submit">
    <a href="?page=gg-search.php&tab=general&reset" class="button"><?php _e("Reset to Defaults", "ggsearch"); ?></a>
    <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save'); ?>">
</p>
</form>