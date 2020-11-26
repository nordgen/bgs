<style>
    .ui-autocomplete {
        max-height: 300px;
        overflow-y: auto;
        width: 250px;
        font-size: 14px;
        /* prevent horizontal scrollbar */
        overflow-x: hidden;
    }

    /* IE 6 doesn't support max-height
     * we use height instead, but this forces the menu to always be this tall
     */
    * html .ui-autocomplete {
        height: 200px;
    }
</style>
<div id="topdiv">
    <div id="logindiv">
        <form name="loginform" action="index.php"
              onsubmit="return <?php echo(isLoggedin() ? "doLogout(this)" : "doLogin(this)"); ?>" method="post"><input
                    type="hidden" name="do">

            <table id="logintab">
                <?php if (!isLoggedin()) { ?>
                    <tr>
                        <td><label for="user">Username</label></td>
                        <td><input id="user" name="user" type="text" size="12"></td>
                    </tr>
                    <tr>
                        <td><label for="pwd">Password<label</td>
                        <td><input id="pwd" name="pwd" type="password" size="12"></td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td><input type="submit" name="login" id="login" value="Log in">
                        </td>
                    </tr>
                <?php } else { ?>
                    <tr>
                        <td>Welcome <?php echo $_SESSION['isloggedin']['username']; ?></td>
                    </tr>
                    <tr>
                        <td><input type="submit" name="logout" id="login" value="Log out"></td>
                    </tr>
                <?php } ?>
            </table>

        </form>
    </div>

    <div id="navdiv">
        <div id="topnav">
            <a href="index.php" class="leftnavitem">[Home]</a>
            <a href="index.php?pg=bgs_tables&m=bgs" class="rightnavitem">[BGS&nbsp;table]</a>
            <a href="index.php?pg=bgs_tables&m=loc" class="rightnavitem">[Loci&nbsp;table]</a>
            <a href="index.php?pg=ul_data" class="rightnavitem">[Background&nbsp;data]</a>
<?php if(hasAllRoles(array("user_admin"))) { ?>
    <a href="index.php?pg=<?php echo(urlencode("admin/bgs_useradmin")); ?>" class="rightnavitem">[User admin]</a>
<?php } ?>
        </div>
        <form name="topform" action="index.php" method="get">
            <input type="hidden" name="act">
            <!-- General search -->
            <label id="searchlabel" for="keywordsearch">Search for gene, locus, phenotype or BGS number:</label>
            <input type="text" id="keywordsearch" name="kwl" style="width:250px; margin-top:5px">
            <input type="hidden" name="kwk" id="kwk">

            <script>
                $("#keywordsearch").autocomplete({
                    source: "keywordsearch.php",
                    minLength: 2,
                    select: function (event, ui) {
                        event.preventDefault();
                        $("#keywordsearch").val(ui.item.label);
                        $("#kwk").val(ui.item.value);
                        doSearch(document.topform, "kws");
                    },
                    focus: function (event, ui) {
                        event.preventDefault();
                        $("#keywordsearch").val(ui.item.label);
                    }
                });
            </script>
        </form>
    </div>

</div>



