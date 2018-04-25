<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location:index.php");
} else {
    $user_id = $_SESSION['id'];
}

require_once('system/data.php');
require_once('system/security.php');

$page = 'add_alters';

if (isset($_GET['action'])) {
    if ($_GET['action'] == 'edit') {
        $alt = get_single_record('alters', $_GET['id']);
    }
    if ($_GET['action'] == 'delete') {
        $alt = delete_record('alters', $_GET['id']);
        header("Location:add_alters.php");
    }
}
if (isset($_POST['data'])) {
    if ($_POST['data']['id'] != '') {
        update_record('alters', $_POST['data']['id'], $_POST['data']);
    } else {
        add_record('alters', $_POST['data']);
    }
    header("Location:add_alters.php");
}


$alters = get_records('alters');
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
                                    <h4 class="panel-title">Alter</h4>
                                </div>
                                <div class="panel-body col-md-12">

                                    <div class="col-md-6">
                                        <form method="POST" action="">
                                            <table class="table">

                                                <tbody>
                                                    <tr>
                                                        <td>Alter Name</td>
                                                        <td>
                                                            <input type="text" name="data[name]" class="form-control" value="<?php echo @$alt['name']; ?>">
                                                            <input type="hidden" name="data[id]" class="form-control" value="<?php echo @$alt['id']; ?>">
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
                                                        <th colspan="2">Sementic</th>
                                                    </tr>
                                                    <tr>

                                                        <th>Name</th>
                                                        <th>Edit / DELETE</th>

                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    foreach ($alters as $key => $value) {
                                                        ?>
                                                        <tr>
                                                            <td><?php echo $value['name']; ?></td>
                                                            <td>
                                                                <a class="btn btn-warning" href="add_alters.php?action=edit&id=<?php echo $value['id']; ?>">Edit</a>
                                                                <a class="btn btn-danger" onclick="return confirm('Are you sure ?');" href="add_alters.php?action=delete&id=<?php echo $value['id']; ?>">Delete</a>
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