<?php include 'header.php'; ?>

    <!-- Main Content -->
    <main class="container">
        <section class="menu">
            <h2>Mis Favoritos</h2>
            
            <?php
            include 'db.php';
            
            if (!isset($_SESSION['user_id'])) {
                echo '<p>Debes iniciar sesión para ver tus favoritos.</p>';
            } else {
                $user_id = $_SESSION['user_id'];
                
                $sql = "
                SELECT m.*, c.nombre AS categoria 
                FROM Favoritos f 
                JOIN MenuItems m ON f.menu_id = m.id 
                JOIN Categoria c ON m.categoria_id = c.id 
                WHERE f.usuario_id = ?
                ORDER BY c.nombre, m.nombre
                ";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $current_category = '';
                    while($item = $result->fetch_assoc()) {
                        if ($current_category !== $item['categoria']) {
                            if ($current_category !== '') {
                                echo '</div></div>';
                            }
                            $current_category = $item['categoria'];
                            echo '<div class="menu-category">';
                            echo '<h3>' . htmlspecialchars($current_category) . '</h3>';
                            echo '<div class="menu-items">';
                        }
                        
                        echo '<div class="menu-item">';
                        echo '<div class="item-header">';
                        echo '<h4>' . htmlspecialchars($item['nombre']) . '</h4>';
                        echo '<div class="item-actions">';
                        echo '<span onclick="location.href=\'toggle_favorite.php?menu_id=' . $item['id'] . '\'" class="favorite-toggle" title="Quitar de favoritos">';
                        echo '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">';
                        echo '<path d="M18 6L6 18M6 6l12 12"/>';
                        echo '</svg>';
                        echo '</span>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                    }
                    echo '</div></div>';
                } else {
                    echo '<p>No tienes platos favoritos aún.</p>';
                }
                
                $stmt->close();
            }
            
            $conn->close();
            ?>
        </section>
    </main>

<?php include 'footer.php'; ?>