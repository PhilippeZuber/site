<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location:index.php");
} else {
    $user_id = $_SESSION['id'];
}

require_once('system/data.php');
require_once('system/security.php');

$page = 'about';


$categories = get_records('category');
$semantic = get_records('semantic');
$alters = get_records('alters');
$words = get_words();
?>
<!doctype html>
<html>
    <?php include './header.php'; ?>
    <body>
        <div class="wrapper">

            <?php include 'sidebar.php'; ?>
            <!-- Page Content Holder -->
            <div id="content">
                <?php include './navigation.php'; ?>

                <div class=container-fluid>
                    <div class="row">
						<div class="col-md-12">
							<img src="images/wortlab_logo.svg" alt="Logo Wortlab">
						</div>
                    </div><!--row-->
                </div><!--/container-->		
            </div><!--/content-->
        </div><!--/wrapper-->

        <!-- jQuery -->
    </body>
</html>