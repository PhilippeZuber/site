<nav class="navbar navbar-fixed-top">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" id="sidebarCollapse" class="btn btn-info navbar-btn">
                <i class="glyphicon glyphicon-align-left"></i>
                <span>Sidebar</span>
            </button>
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <i class="glyphicon glyphicon-align-justify"></i>
            </button>
        </div>
        <div id="navbar" class="collapse navbar-collapse" aria-expanded="false" style="height: 1px;">
            <?php
                if ($_SESSION['role'] == 1 OR $_SESSION['role'] == 2) {/*visible for logged-in only*/
            ?>
            <ul class="nav navbar-nav navbar-right">
                <li><img src="user_img/<?php echo $user['img_src']; ?>" alt="Profilbild" width="50px"/></a></li>
                <li><a href="#" data-toggle="modal" data-target="#UserModal"><?php echo $user['firstname'] . " " . $user['lastname']; ?></a></li>
                <li><a href="login.php"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span> Logout</a></li>
            </ul>
            <?php
                }
            ?>
        </div>
    </div>
</nav>