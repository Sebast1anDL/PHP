<?php include 'header.php'; ?>

    <!-- Main Content -->
    <main class="container">
        <section class="menu">
            <h2>Nuestro Menú</h2>
            
            <?php
            include 'db.php';
            
            $sql_cat = "SELECT * FROM Categoria ORDER BY id";
            $result_cat = $conn->query($sql_cat);
            
            if ($result_cat->num_rows > 0) {
                while($cat = $result_cat->fetch_assoc()) {
                    echo '<div class="menu-category">';
                    echo '<h3>' . htmlspecialchars($cat['nombre']) . '</h3>';
                    echo '<div class="menu-items">';
                    
                    $sql_items = "SELECT * FROM MenuItems WHERE categoria_id = " . $cat['id'];
                    $result_items = $conn->query($sql_items);
                    
                    if ($result_items->num_rows > 0) {
                        while($item = $result_items->fetch_assoc()) {
                            echo '<div class="menu-item">';
                            echo '<div class="item-header">';
                            echo '<h4>' . htmlspecialchars($item['nombre']) . '</h4>';
                            echo '<span class="price">$' . $item['precio'] . '</span>';
                            echo '</div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p>No hay items en esta categoría.</p>';
                    }
                    
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<p>No hay categorías disponibles.</p>';
            }
            
            $conn->close();
            ?>
        </section>
    </main>

<?php include 'footer.php'; ?>
