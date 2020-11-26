<?php
global $Zdb;
if (!hasAllRoles(array("user_admin"))) {
    echo "Not authorized for page.";
    exit();
}
//Get all roles
$q = "SELECT id, name, description FROM bgs_role ORDER BY ord";

try {
    $rs = [];
    $rs = $Zdb->query($q)->getQueryResultSet();
    //echo "<br>".$q."<br>";
} catch (exception $e) {
    echo "Error selecting roles, \$q: " . $q . " - " . $e->getMessage();
}
$roles = array();
foreach ($rs as $row) {
    $roles[$row['id']] = array($row['name'], $row['description']);
}
$tabcols = count($roles) + 6;
?>

<form name="userAdminForm" action="index.php?pg=<?php echo(urlencode("admin/bgs_useradmin")); ?>" method="post"
      enctype="multipart/form-data">
    <input type="hidden" name="act">
    <input type="hidden" name="userid">
    <h1>User admin</h1>

    <table class="useradmintab minimize">
        <tr class="tabhead">
            <td>Username</td>
            <td>Real name</td>
            <?php foreach ($roles as $r) { ?>
                <td><span title="<?php echo $r[1]; ?>"><?php echo $r[0]; ?></span></td>
            <?php } ?>
            <td>&nbsp;</td>
            <td>Password</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <?php
        //Get all users with roles
        $q = "SELECT u.id AS userid, u.username, u.real_name, u.comment FROM bgs_user u WHERE u.deleted=0 ORDER BY u.username";

        try {
            $rs = [];
            $rs = $Zdb->query($q)->getQueryResultSet();
            //echo "<br>".$q."<br>";
        } catch (exception $e) {
            echo "Error selecting users, \$q: " . $q . " - " . $e->getMessage();
        }

        foreach ($rs as $row) {
            $uroles = array();
            //Get all roles for this user
            //Get all users with roles
            $q2 = "SELECT ur.role_id AS roleid FROM bgs_user_role ur WHERE ur.user_id=" . $row['userid'];

            try {
                $rs2 = [];
                $rs2 = $Zdb->query($q2)->getQueryResultSet();
                //echo "<br>".$q."<br>";
            } catch (exception $e) {
                echo "Error selecting user-role mappings, \$q: " . $q . " - " . $e->getMessage();
            }

            foreach ($rs2 as $row2) {
                $uroles[] = $row2['roleid'];
            }

            ?>
            <tr>
                <td><label for="realname_<?php echo $row['userid']; ?>"><?php echo $row['username']; ?></label></td>
                <td><input id="realname_<?php echo $row['userid']; ?>" type="text" size="15"
                           name="realname_<?php echo $row['userid']; ?>" value="<?php echo $row['real_name']; ?>"></td>
                <?php foreach (array_keys($roles) as $rid) { ?>
                    <td><input type="checkbox" name="role_<?php echo $row['userid'] . "_" . $rid; ?>"
                               value="1" <?php echo(in_array($rid, $uroles) ? "checked" : ""); ?> ></td>
                <?php } ?>
                <td><input type="button" name="updusr_<?php echo $row['userid']; ?>" value="Update"
                           onclick="updateUser(<?php echo $row['userid']; ?>,document.userAdminForm)"></td>
                <td><input type="text" size="15" name="password_<?php echo $row['userid']; ?>" value="*******"></td>
                <td><a href="javascript:changePassword(<?php echo $row['userid']; ?>,document.userAdminForm)">Change
                        password</a></td>
                <td>
                    <a href="javascript:deleteUser(<?php echo $row['userid']; ?>,'<?php echo $row['username']; ?>',document.userAdminForm)">Delete</a>
                </td>
            </tr>
        <?php } //End loop through users ?>
        <tr class="tabhead">
            <td colspan="<?php echo $tabcols; ?>"><label for="username_new">New user</label></td>
        </tr>
        <tr>
            <td><input id="username_new" type="text" size="15" name="username_new" value=""></td>
            <td><input type="text" size="15" name="realname_new" value=""/></td>
            <?php foreach (array_keys($roles) as $rid) { ?>
                <td><input type="checkbox" value="1" name="role_new_<?php echo $rid; ?>"></td>
            <?php } ?>
            <td><input type="text" size="15" name="password_new" value="Choose password"></td>
            <td colspan="3"><input type="button" name="addnew" value="Add new user"
                                   onclick="addUser(document.userAdminForm)"></td>
        </tr>
    </table>
</form>