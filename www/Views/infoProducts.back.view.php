<h1 class="centered">Avis du produit n° <?= $product ?></h1>
<h2 class="centered">Nom du produit : <?= $product_name ?></h2>

<div class="container">
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <div class="jumbotron">
                <table id="table" class="display">
                    <thead>
                    <tr>
                        <th>ID du commentaire</th>
                        <th>Note</th>
                        <th>Commentaire</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($datas as $review): ?>
                        <tr>
                            <td><?= $review["id_r"] ?></td>
                            <td><?= $review["mark"]. "/5" ?></td>
                            <td><?= $review["commentary"] ?></td>
                            <td><?= $review["email"] ?></td>
                            <td>
                                <a href="/admin/del-review?id=<?= $review["id_review"] ?>" class="button button--alert">
                                    <i class="bi bi-trash-fill"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="../public/js/datatable.js"></script>