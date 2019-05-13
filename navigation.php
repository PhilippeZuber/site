<nav class="navbar navbar-fixed-top">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" id="sidebarCollapse" class="btn btn-info navbar-btn">
                <i class="glyphicon glyphicon-align-left"></i>
                <span>Sidebar</span>
            </button>
        </div>
        <div class="collapse navbar-collapse"  id="bs-example-navbar-collapse-1">
            <?php
                if ($_SESSION['role'] == 1 OR $_SESSION['role'] == 2) {/*visible for logged-in only*/
            ?>
            <ul class="nav navbar-nav navbar-right">
                <li><img src="user_img/<?php echo $user['img_src']; ?>" alt="Profilbild" width="50px"/></a></li>
                <li><a href="#" data-toggle="modal" data-target="#UserModal"><?php echo $user['firstname'] . " " . $user['lastname']; ?></a></li>
                <li><a href="index.php"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span> Logout</a></li>
            </ul>
            <?php
                }
            ?>
        </div>
    </div>
</nav>