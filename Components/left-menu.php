<?php
$qryParent = "select distinct MenuId,MenuLavel,Menuurl,isLink,Parent,MenuOrder from menudefine where isLink = 'False' AND 
   MenuId in(select MenuId from rolemenu where permission=1 and RoleId in(select RoleId from userrole 
   where UserID in(select id from userinfo Where UserName = ?))) order by MenuOrder asc";
$stmQryNavMenuParent = $app->getDBConnection()->fetchAll($qryParent, $loggedUserName);

if ($_GET['parent'] != '') {
    $currentparent = $app->cleanInput($_GET['parent']);
    $currentMenuURL = getValue("menudefine", "MenuURL", "MenuId='$currentparent'");
	list($currentMenuID, $filePath) = explode("/", $currentMenuURL);
} else {
	$currentMenuID = "";
}

foreach ($stmQryNavMenuParent as $navMenuRow) {
    $pageIDParent = $navMenuRow->MenuId;
    $menuCaptionParent = $navMenuRow->MenuLavel;
    $menuUrlParent = $navMenuRow->Menuurl;
    $isLinkParent = $navMenuRow->isLink;
    $menuParent = $navMenuRow->Parent;
    $menuOrderParent = $navMenuRow->MenuOrder;
    ?>

    <li class="dropdown">
        <a class="nav-link dropdown-toggle" href="#" <?=((strtolower($currentMenuID) == strtolower($pageIDParent) || (strtolower($currentMenuID) == strtolower($menuCaptionParent))) ? "style='color: #0088CC;'" : "");?>>
            <?php echo $menuCaptionParent; ?>
        </a>
        <ul class="dropdown-menu">
            <?php
            $qryChild = "select distinct MenuId,MenuLavel,Menuurl,isLink,Parent,MenuOrder from menudefine 
            where Parent = ? and isLink = 'True' AND MenuId in(select MenuId from rolemenu 
            where permission=1 and RoleId in(select RoleId from userrole where UserID in(select id from userinfo Where UserName = ?))) 
            order by MenuOrder asc ";
            $stmQryNavMenuChild = $app->getDBConnection()->fetchAll($qryChild, $menuOrderParent, $loggedUserName);

            foreach ($stmQryNavMenuChild as $navMenuRowChild) {
                $pageIDChild = $navMenuRowChild->MenuId;
                $menuCaptionChild = $navMenuRowChild->MenuLavel;
                $menuUrlChild = $navMenuRowChild->Menuurl;
                $isLinkChild = $navMenuRowChild->isLink;
                $menuChild = $navMenuRowChild->Parent;
                $menuOrderChild = $navMenuRowChild->MenuOrder;
                ?>
                <li>
                    <a class="nav-link" href='../index.php?parent=<?php echo $pageIDChild; ?>'>
                        <?php echo $menuCaptionChild; ?>
                    </a>
                </li>
                <?php
            }
            ?>
        </ul>
    </li>
    <?php
}
?>

