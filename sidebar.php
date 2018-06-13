<!-- Sidebar Holder -->
<nav id="sidebar">
    <div class="sidebar-header">
        <h3>WORTLABOR</h3>
        <strong>WL</strong>
    </div>

    <ul class="list-unstyled components">
        <?php
        if ($_SESSION['role'] == 1) {/*visible for admin only*/
            ?>
			<li>
				<a href="#pageSubmenu1" data-toggle="collapse" aria-expanded="false">
					<i class="glyphicon glyphicon-wrench"></i>
					Wörter verwalten
				</a>
				<ul class="collapse list-unstyled" id="pageSubmenu1">
					<li class="<?php echo $page=='add_word' ? 'active' : ''; ?>">
						<a href="add_word.php">
							<i class="glyphicon glyphicon-plus"></i>
							Wörter
						</a>
   			        </li>
					<li class="<?php echo $page=='add_cat' ? 'active' : ''; ?>">
						<a href="add_cat.php">
							<i class="glyphicon glyphicon-plus"></i>
							Wortarten
						</a>
					</li>
					<li class="<?php echo $page=='add_semantic' ? 'active' : ''; ?>">
						<a href="add_semantic.php">
							<i class="glyphicon glyphicon-plus"></i>
							Themen
						</a>
					</li>
					<li class="<?php echo $page=='add_alters' ? 'active' : ''; ?>">
						<a href="add_alters.php">
							<i class="glyphicon glyphicon-plus"></i>
							Alter
						</a>
					</li>
				</ul>
			</li>
            <?php
        }
        ?>
        <li class="<?php echo $page=='search' ? 'active' : ''; ?>">
            <a href="search.php">
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
            <a href="#pageSubmenu2" data-toggle="collapse" aria-expanded="false">
                <i class="glyphicon glyphicon-info-sign"></i>
                Über Wortlabor
            </a>
            <ul class="collapse list-unstyled" id="pageSubmenu2">
                <li><a href="#">Seite 1</a></li>
                <li><a href="#">Seite 2</a></li>
            </ul>
        </li>
		<?php
        if ($_SESSION['role'] == 2) {/*visible for non-admin only*/
            ?>
        <li>
            <a href="send_word.php">
                <i class="glyphicon glyphicon-send"></i>
                Wörter einsenden
            </a>
        </li>
		<?php
        }
        ?>
        <li>
            <a href="index.php"aria-hidden="true">
                <i class="glyphicon glyphicon-log-out"></i>
                Logout
            </a>
        </li>
    </ul>
	<!-- Only visible when sidebar not collapsed
    <ul class="list-unstyled CTAs">
        <li><a href="#" class="btn1">Button 1</a></li>
        <li><a href="#" class="btn2">Button 2</a></li>
    </ul>-->
</nav>