<!-- Sidebar Holder -->
<nav id="sidebar">
    <div class="sidebar-header">
    	<a href="index.php">
        	<h3><img src="wortlab_button.svg" alt="WORTLAB"></h3>
        	<strong><img src="wortlab_button.svg" alt="WORTLAB"></strong>
        </a>
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
                    <li class="<?php echo $page=='add_pending_word' ? 'active' : ''; ?>">
						<a href="add_pending_word.php">
							<i class="glyphicon glyphicon-plus"></i>
							Pendente Wörter
						</a>
					</li>
				</ul>
			</li>
			<li class="<?php echo $page=='newsletter' ? 'active' : ''; ?>">
				<a href="send_newsletter.php">
					<i class="glyphicon glyphicon-envelope"></i>
					Newsletter
				</a>
			</li>
            <?php
        }
        ?>
        <?php
        if ($_SESSION['role'] == 1 OR $_SESSION['role'] == 2) {/*visible for logged-in only*/
            ?>
        <li class="<?php echo $page=='search' ? 'active' : ''; ?>">
            <a href="search.php">
                <i class="glyphicon glyphicon-search"></i>
                Suche
            </a>
        </li>
        <?php
        }
        ?>
        <?php
        if (!($_SESSION['role'] == 1 OR $_SESSION['role'] == 2)) {/*visible for non logged-in only*/
            ?>
        <li class="<?php echo $page=='search' ? 'active' : ''; ?>">
            <a href="index.php">
                <i class="glyphicon glyphicon-search"></i>
                Suche
            </a>
        </li>
        <?php
        }
        ?>
        <?php
        if ($_SESSION['role'] == 1 OR $_SESSION['role'] == 2) {/*visible for logged-in only*/
            ?>
        <li class="">
            <a href="#" data-toggle="modal" data-target="#UserModal">
                <i class="glyphicon glyphicon-user"></i>
                <?php echo $user['firstname'] . " " . $user['lastname']; ?>
            </a>
        </li>
        <?php
        }
        ?>
        <?php
        if (!($_SESSION['role'] == 1 OR $_SESSION['role'] == 2)) {/*visible for non logged-in only*/
            ?>
        <li class="">
            <a href="login.php">
                <i class="glyphicon glyphicon-user"></i>
                Einloggen / Registrieren
            </a>
        </li>
        <?php
        }
        ?>
        <li class="<?php echo $page=='jobs' ? 'active' : ''; ?>">
            <a href="jobs.php">
                <i class="glyphicon glyphicon-briefcase"></i>
                Stellen
			</a>
        </li>
        <li class="<?php echo $page=='about' ? 'active' : ''; ?>">
            <a href="about.php">
                <i class="glyphicon glyphicon-info-sign"></i>
                Über Wortlab
			</a>
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
        <?php
        if ($_SESSION['role'] == 1 OR $_SESSION['role'] == 2) {/*visible for logged-in only*/
            ?>
        <li>
            <a href="login.php"aria-hidden="true">
                <i class="glyphicon glyphicon-log-out"></i>
                Logout
            </a>
        </li>
        <?php
        }
        ?>
    </ul>
    <?php
        if (!($_SESSION['role'] == 1 OR $_SESSION['role'] == 2)) {/*visible for non logged-in only*/
    ?>
        <!-- Only visible when sidebar not collapsed-->
        <ul class="list-unstyled CTAs">
            <li><a href="login.php" class="btn1">Registriere dich kostenlos und erhalte den Filter lauttreu sowie die Funktion Buchstaben auszuschliessen. Ausserdem kannst du dann Memories aus den Wörtern erstellen! :-)</a></li>
        </ul>
    <?php
        }
    ?>
    <?php
        if ($_SESSION['role'] == 1 OR $_SESSION['role'] == 2) {/*visible for logged-in only*/
            ?>
        <!-- Only visible when sidebar not collapsed-->
        <!--<ul class="list-unstyled CTAs">
            <li>
            <div class="thumbnail">
                <img src="img/braendi_picto.jpg" alt="Spiel Brändi Picto">
                <div class="caption">
                    <h3>Verlosung</h3>
                    <p>Du nimmst als registrierte Benutzerin automatisch an der Verlosung der Spiele Brändi Picto™ und Brändi Picto Plus™ teil. Die Gewinnerin wird am 28. Februar ausgelost.</p>
                    <p><a href="https://www.braendi-shop.ch/de/A~SB.A25-03" class="btn btn-primary" role="button">Infos zum Spiel</a></p>
                </div>
            </div>
            </li>
        </ul>-->
        <?php
        }
    ?>
</nav>