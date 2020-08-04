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

    if(isset($_GET['delete'])){
        $sql_delete = "DELETE FROM " . $table . " WHERE id = " . $_GET['delete'];
        $stmt = $conn->prepare($sql_delete);
        $stmt->execute();
        header("Location: /ProjectManagerPHP/?path=" . $_GET['path']);
    }

    if(isset($_POST['update'])){
        $sql_update = "UPDATE " . $table 
                    . " SET id=" . $_POST['id'] 
                    . ", name='" . $_POST['name'] 
                    . (isset($_POST['proj_id'])? "', proj_id='" . $_POST['proj_id'] : "")
                    . "' WHERE id=" . $_GET['update'];
        $stmt = $conn->prepare($sql_update);
        $stmt->execute();
        header("Location: /ProjectManagerPHP/?path=" . $_GET['path']);
    }

    if(isset($_POST['ADD'])){
        print($_POST['name']);
        $sql_add = "INSERT INTO " . $table . " (`name`) VALUES (?)"; 
        $stmt = $conn->prepare($sql_add);
        $stmt -> bind_param("s", $_POST['name']);
        $stmt->execute();
        header("Location: /ProjectManagerPHP/?path=" . $_GET['path']);
    }

    $sql = "SELECT " 
                . $table. ".id, " 
                . $table.".name, GROUP_CONCAT(" . ($table === 'projektai' ? 'darbuotojai' : 'projektai' ) . ".name SEPARATOR \", \")" . 
            " FROM " . $table . 
            " LEFT JOIN " . ($table === 'projektai' ? 'darbuotojai' : 'projektai') . 
            " ON " . ($table === 'projektai' ? 'darbuotojai.proj_id = projektai.id' : 'darbuotojai.proj_id = projektai.id') .
            " GROUP BY " . $table . ".id;";
    
    /*  SELECT darbuotojai.id, darbuotojai.name, projektai_sql.name FROM darbuotojai
        JOIN projektai_sql ON darbuotojai.proj_id = projektai_sql.id;

        SELECT projektai_sql.id, projektai_sql.name, darbuotojai.name FROM projektai_sql
        JOIN darbuotojai ON darbuotojai.proj_id = projektai_sql.id; */ 

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
            select {
                display: inline-block !important;
            }
        </style>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    </head>
    <body>
        <header class="mdl-layout__header mdl-layout__header--waterfall mdl-layout__header--waterfall-hide-top" id="djpsych-header">
            <nav>
                <div class="nav-wrapper">
                    <a href="?path=projektai_sql" class="brand-logo right" style="padding-right: 20px">Projekto valdymas</a>
                    <ul id="nav-mobile" class="left">
                        <li><a href="?path=projektai">Projektai</a></li>
                        <li><a href="?path=darbuotojai">Darbuotojai</a></li>
                    </ul>
                </div>
            </nav>
        </header>
        <main style="padding: 30px" class="mdl-layout__content main-layout">
            <?php
                echo '<table><th>Id</th><th>Name</th><th>' . ($table === 'projektai' ? 'Darbuotojai' : 'Projektai') . '</th><th>Actions</th>';
                while ($stmt->fetch()){
                    echo "<tr>
                            <td>" . $id . "</td>
                            <td>" . $mainEntityName . "</td>
                            <td>" . $relatedEntityName . "</td>
                            <td>
                                <button><a href=\"?path=" . $table . "&delete=$id\">DELETE</a></button>
                                <button><a href=\"?path=" . $table . "&update=$id\">UPDATE</a></button>
                            </td>
                        </tr>";
                }
                echo '</table>';

                if(isset($_GET['update'])){
                    $projektai_sql = mysqli_query($conn, "SELECT id, name FROM Projektai");
                    $projektai = [];
                    if (mysqli_num_rows($projektai_sql) > 0)
                        while($projektas = mysqli_fetch_assoc($projektai_sql))
                            $projektai[$projektas['id']] = $projektas['name'];

                    $sql_update = "SELECT id, name FROM " . $table . " WHERE id = " . $_GET['update'];
                    $stmt = $conn->prepare($sql_update);
                    $stmt->execute();
                    $stmt->bind_result($id, $mainEntityName);
                    
                    while ($stmt->fetch()){
                        echo "<br><br><form style=\"max-width: 150px\" action=\"\" method=\"POST\">
                            <input type=\"text\" name=\"id\" value=\"" . $id . "\">
                            <input type=\"text\" name=\"name\" text value=\"" . $mainEntityName . "\">";
                            if($_GET['path'] === 'darbuotojai'){
                                echo "<select name=\"proj_id\">
                                <option value=\"\" disabled selected>Projektas:</option>";
                                foreach($projektai as $p_id => $p_name)
                                    echo "<option value=\"$p_id\">$p_name</option>";
                                echo "</select>";
                            }
                            echo "<input type=\"submit\" value=\"UPDATE\" name=\"update\">
                        </form>";
                    }
                } else
                    echo "<br><br><form style=\"max-width: 150px\" action=\"\" method=\"POST\">
                            <input type=\"text\" name=\"name\" value=\"\" placeholder=\"" 
                                . ($_GET['path'] === 'projektai' ? 'Projekto pavadinimas' : 'Darbuotojo vardas')  . "\">
                            <input type=\"submit\" value=\"ADD " 
                                . ($_GET['path'] === 'projektai' ? 'Projektas' : 'Darbuotojas') . "\" name=\"ADD\">
                        </form>";
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
