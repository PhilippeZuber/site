<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location:index.php");
} else {
    $user_id = $_SESSION['id'];
}

require_once('system/data.php');
require_once('system/security.php');

$page = 'add_cat';

if (isset($_GET['action'])) {
    if ($_GET['action'] == 'edit') {
        $cat = get_single_record('category', $_GET['id']);
    }
    if ($_GET['action'] == 'delete') {
        $cat = delete_record('category', $_GET['id']);
        header("Location:add_cat.php");
    }
}
if (isset($_POST['data'])) {
    if ($_POST['data']['id'] != '') {
        update_record('category', $_POST['data']['id'], $_POST['data']);
    } else {
        add_record('category', $_POST['data']);
    }
    header("Location:add_cat.php");
}


$categories = get_records('category');
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
                        <!-- begin col-6 -->
                        <div class="col-md-12">
                            <!-- begin panel -->
                            <div class="panel panel-inverse" >
                                <div class="panel-heading">
                                    <div class="panel-heading-btn">
                                    </div>
                                    <h4 class="panel-title">Category</h4>
                                </div>
                                <div class="panel-body col-md-12">

                                    <div class="col-md-6">
                                        <form method="POST" action="">
                                            <table class="table">

                                                <tbody>
                                                    <tr>
                                                        <td>Category Name</td>
                                                        <td>
                                                            <input type="text" name="data[name]" class="form-control" value="<?php echo @$cat['name']; ?>">
                                                            <input type="hidden" name="data[id]" class="form-control" value="<?php echo @$cat['id']; ?>">
                                                        </td>

                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td>
                                                            <input type="submit"  class="form-control btn btn-primary" value="Submit">
                                                        </td>

                                                    </tr>
                                                </tbody>
                                            </table>

                                        </form>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="table-responsive">

                                            <table id="data-table1" class="table table-striped table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th colspan="2">Category</th>
                                                    </tr>
                                                    <tr>

                                                        <th>Name</th>
                                                        <th>Edit / DELETE</th>

                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    foreach ($categories as $key => $value) {
                                                        ?>
                                                        <tr>
                                                            <td><?php echo $value['name']; ?></td>
                                                            <td>
                                                                <a class="btn btn-warning" href="add_cat.php?action=edit&id=<?php echo $value['id']; ?>">Edit</a>
                                                                <a class="btn btn-danger" onclick="return confirm('Are you sure ?');" href="add_cat.php?action=delete&id=<?php echo $value['id']; ?>">Delete</a>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>

                                        </div>
                                    </div>


                                </div>
                            </div>
                        </div>
                    </div>
                </div><!--/container-->		
            </div><!--/content-->
        </div><!--/wrapper-->

        <!-- jQuery -->
        <script>

            $(document).ready(function ()
            {
                $('#data-table1').DataTable();
            });


        </script>
    </body>
</html>