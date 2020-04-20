<?php
    $table = 'darbuotojai';
    $servername = "localhost";
    $username = "root";
    $password = "mysql";
    $dbname = "projmanager";

    if(isset($_GET['path']) and $_GET['path'] !== $table){
        if($_GET['path'] == 'darbuotojai' or $_GET['path'] == 'projektai')
            $table = $_GET['path'];
    }

    $conn = mysqli_connect($servername, $username, $password, $dbname);
    if (!$conn) 
        die("Connection failed: " . mysqli_connect_error());

    $sql = "SELECT " 
                . $table. ".id, " 
                . $table.".name, GROUP_CONCAT(" . ($table === 'projektai' ? 'darbuotojai' : 'projektai' ) . ".name SEPARATOR \", \")" . 
            " FROM " . $table . 
            " LEFT JOIN " . ($table === 'projektai' ? 'darbuotojai' : 'projektai') . 
            " ON " . ($table === 'projektai' ? 'darbuotojai.proj_id = projektai.id' : 'darbuotojai.proj_id = projektai.id') .
            " GROUP BY " . $table . ".id;";
    
    /*
        SELECT darbuotojai.id, darbuotojai.name, projektai.name FROM darbuotojai
        JOIN projektai ON darbuotojai.proj_id = projektai.id;

        SELECT projektai.id, projektai.name, darbuotojai.name FROM projektai
        JOIN darbuotojai ON darbuotojai.proj_id = projektai.id;
    */ 
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($id, $mainEntityName, $relatedEntityName);
?>
<html>
    <head>
        <style>
            body {
                display: flex;
                min-height: 100vh;
                flex-direction: column;
            }
            main {
                flex: 1 0 auto;
            }
        </style>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    </head>
    <body>
        <header class="mdl-layout__header mdl-layout__header--waterfall mdl-layout__header--waterfall-hide-top" id="djpsych-header">
            <nav>
                <div class="nav-wrapper">
                    <a href="?path=projektai" class="brand-logo right" style="padding-right: 20px">Projekto valdymas</a>
                    <ul id="nav-mobile" class="left">
                        <li><a href="?path=projektai">Projektai</a></li>
                        <li><a href="?path=darbuotojai">Darbuotojai</a></li>
                    </ul>
                </div>
            </nav>
        </header>
        <main style="padding: 30px" class="mdl-layout__content main-layout">
            <?php
                echo '<table><th>Id</th><th>Name</th><th>' . ($table === 'projektai' ? 'Darbuotojai' : 'Projektai') . '</th>';
                while ($stmt->fetch()){
                    echo "<tr><td>" . $id . "</td><td>" . $mainEntityName . "</td><td>" . $relatedEntityName . "</td></tr>";
                }
                echo '</table>';
            ?>
        </main>
        <footer class="page-footer">
            <div class="container">
                <div class="row">
                    <div class="col l6 s12">
                        <h5 class="white-text">Footer</h5>
                    </div>
                </div>
            </div>
            <div class="footer-copyright">
                <div class="container">
                © 2014 Copyright Text
                <a class="grey-text text-lighten-4 right" href="#!">Link to nowhere</a>
                </div>
            </div>
        </footer>
    </body>
</html>
<?php
    $stmt->close();
    mysqli_close($conn);
?>
