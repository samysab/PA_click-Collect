<section>
    <div class="container">
        <h1><?= strtoupper($name) ?></h1>
        <?php
        if (isset($description)){
            echo "<p>".$description."</p>";
        }
        ?>
    </div>
</section>

<section>
    <div class="container">
        <div class="row">

            <?php
            foreach ($products as $product):
            ?>
            <div class="col-lg-3 col-md-3  col-sm-6 col">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title"> <?= $product['name'] ?></h3>
                        <a href="/produit?id=<?= $product['id']?>"><button class="button button--blue">En savoir plus</button></a>
                    </div>
                </div>
            </div>


            <?php endforeach;?>
        </div>
    </div>
</section>

<section>
    <div class="container">
    <nav>
        <ul class="pagination">

            <li class="page-item <?= ($page == 1) ? "disabled" : "" ?>">
                <a href="/collections?name=<?= $name ?>&page=<?= $page - 1 ?>" class="page-link">Précédente</a>
            </li>
            <?php for($nbpage = 1; $nbpage <= $pages; $nbpage++): ?>

                <li class="page-item <?= ($page == $nbpage) ? "active" : "" ?>">
                    <a href="/collections?name=<?= $name ?>&page=<?= $nbpage ?>" class="page-link"><?= $nbpage ?></a>
                </li>
            <?php endfor ?>

            <li class="page-item <?= ($page == $pages) ? "disabled" : "" ?>">
                <a href="/collections?name=<?= $name ?>&page=<?= $page + 1 ?>" class="page-link">Suivante</a>
            </li>
        </ul>
    </nav>
    </div>
</section>
