<!-- Sidebar Holder -->
<nav id="sidebar">
    <div class="sidebar-header">
        <h3>WORTLABOR</h3>
        <strong>WL</strong>
    </div>

    <ul class="list-unstyled components">
        <?php
        if ($_SESSION['role'] == 1) {
            ?>
            <li class="<?php echo $page=='add_word' ? 'active' : ''; ?>">
                <a href="add_word.php">
                    <i class="glyphicon glyphicon-plus"></i>
                    Add Word
                </a>
            </li>
            <li class="<?php echo $page=='add_cat' ? 'active' : ''; ?>">
                <a href="add_cat.php">
                    <i class="glyphicon glyphicon-plus"></i>
                    Add Word Category
                </a>
            </li>
            <li class="<?php echo $page=='add_sementic' ? 'active' : ''; ?>">
                <a href="add_sementic.php">
                    <i class="glyphicon glyphicon-plus"></i>
                    Add Semantic fields
                </a>
            </li>
            <li class="<?php echo $page=='add_alters' ? 'active' : ''; ?>">
                <a href="add_alters.php">
                    <i class="glyphicon glyphicon-plus"></i>
                    Alter
                </a>
            </li>

            <?php
        }
        ?>
        <li class="<?php echo $page=='search' ? 'active' : ''; ?>">
            <a href="suche.php">
                <i class="glyphicon glyphicon-search"></i>
                Suche
            </a>
        </li>
        <li class="">
            <a href="#" data-toggle="modal" data-target="#myModal">
                <i class="glyphicon glyphicon-user"></i>
                <?php echo $user['firstname'] . " " . $user['lastname']; ?>
            </a>
        </li>
        <li>
            <a href="#pageSubmenu" data-toggle="collapse" aria-expanded="false">
                <i class="glyphicon glyphicon-info-sign"></i>
                Über Wortlabor
            </a>
            <ul class="collapse list-unstyled" id="pageSubmenu">
                <li><a href="#">Seite 1</a></li>
                <li><a href="#">Seite 2</a></li>
            </ul>
        </li>
        <li>
            <a href="#">
                <i class="glyphicon glyphicon-send"></i>
                Kontakt
            </a>
        </li>
        <li>
            <a href="index.php"aria-hidden="true">
                <i class="glyphicon glyphicon-log-out"></i>
                Logout
            </a>
        </li>
    </ul>

    <ul class="list-unstyled CTAs">
        <li><a href="#" class="btn1">Button 1</a></li>
        <li><a href="#" class="btn2">Button 2</a></li>
    </ul>
</nav>