<section>
    <div class="container">
        <h1>Modification du produit : <?= $produits[0]["productName"]?? $p[0]["productName"]?></span></h1>
     </div>
</section>


<section>
    <div class="container">
        <div class="row" id="status" style="display: none"></div>
        <div class="row jumbotron">
            <div class="col-md-6 col-lg-6 col-sm-6 col">
                <div class="form_align--top">
                    <label class="label">Nom *</label>
                    <input class="input" value="<?= $produits[0]["productName"]?? $p[0]["productName"]?>" type="text" id="product_name" placeholder="Chapeau" required="required">
                </div>

                <div class="form_align--top mt-1">
                    <label class="label">Description</label>
                    <textarea class="input" type="text" id="description" placeholder="..."><?= $produits[0]["description"]?? $p[0]["description"] ?></textarea>
                </div>
            </div>

            <div class="col-md-6 col-lg-6 col-sm-6 col">
                <div class="form_align--top">
                    <label class="label">Catégorie *</label>
                    <select class="input" id="category">
                        <?php foreach ($categories as $category):?>
                            <option value="<?= $category["id"]?>"><?= $category["name"] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mt-3">
                    <?php if (isset($produits[0]["type"]) && isset($p[0]["type"])): ?>
                        <input id="variant" type="checkbox" value="1" 
                        <?= ($produits[0]["type"] || $p[0]["type"]) ? "checked" :  ""?> onclick="hasVariants()">

                        <label>Ce produit comporte plusieurs variantes, ex. différentes tailles ou couleurs. *</label>
                    <?php else: ?>

                        <input id="variant" type="checkbox" value="1" onclick="hasVariants()">
                        <label>Ce produit comporte plusieurs variantes, ex. différentes tailles ou couleurs. *</label>

                    <?php endif;?>
                </div>
            </div>
        </div>
    </div>

    <div class="container" id='attr_container' style="display: none;">
        <div class="row">
            <div class="col col-md-12 col-sm-12 col-lg-12">
                <div class="jumbotron">

                    <div class="">
                        <h3>Les attributs</h3>
                        <hr>
                    </div>


                    <div class="row">
                        <div class="col-md-6 col-lg-6 col-sm-6 col">
                            <div id="blockAttributes" class="attributes">
                                <?php  foreach ($attributes as $attribute): ?>
                                    <div class="mb-1">
                                        <input class="checked" name="attributs" id="attr-<?= $attribute['id'] ?>" type="checkbox" value="<?= $attribute['id'] ?>" onclick="getSelectedAttributes(<?= $attribute['id']?>)">
                                        <label id="lab-<?= $attribute['id'] ?>"><?= $attribute['name'] ?></label>
                                    </div>
                                <?php endforeach;?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container" id='var_container' style="display: none;">
        <div class="row">
            <div class="col col-md-12 col-sm-12 col-lg-12">
                <div class="jumbotron">

                    <div class="">
                        <h3>Valeurs</h3>
                        <hr>
                    </div>
                    <div id="selectedAttributes"></div>

                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row mt-1" id="btns">
            <button id="addVar" class="button button--blue mr-1" onclick="updateP(<?= $p[0]["id"] ?>)">Enregistrer</button>
            <button id="addVar" class="button button--warning mr-1" onclick="add_variante()">Recréer les variantes</button>
            <a class="button button--alert" href="delete-variantes?id=<?= $_GET['id'] ?>">Supprimer les variantes</a>
        </div>
    </div>
    <div class="container">
        <?php foreach($datas_inputs as $input):?>
            <div name="comb" class="row jumbotron mt-2">
                <label class='col col-sm-3 col-md-2 col-lg-4'>
                    <?php foreach($input as $value): ?>
                        <?= $value["nameAttr"]; ?>
                    <?php endforeach;?>
                </label>
                <input type='number' value="<?= $input[0]['stock']?>" class='input col-sm-3 col-md-3 col-lg-3 mr-1' id='stock-<?= $input[0]['idGroup']  ?>' placeholder='Stock' /> 
                <input type='number' value="<?= $input[0]['price']?>" class='input col-sm-3 col-md-3 col-lg-3' id='price-<?= $input[0]['idGroup']  ?>' placeholder='Prix' />
                <button class="col col-sm-3 col-md-4 col-lg-1 button button--success" onclick="update_var(<?= $input[0]['idGroup']?>)">Modifier</button>
            </div>
            <div class="centered mt-1">
                <?php if(!empty($input[0]['picture'])): ?> 
                    <div>
                        <img style="width: 200px;" src="../images/products/<?=$input[0]['picture']?>" />
                        <label for="file" class="label-file"><i class="fas fa-file-upload"></i></label>
                        <input id="file" class="input-file" type="file">  
                        <a class="button button--alert" href="/admin/del-picture-variante?id=<?= $input[0]["idGroup"] ?>">
                            <i class="fas fa-trash"></i>
                        </a>                      
                    </div>
                <?php else: ?>
                    <div class="row"><p>Pas d'image pour cette variante <button class="button button--success">+</button></p></div>
                <?php endif; ?>
            </div>
        <?php endforeach;?>
    </div>

    <div class="container">
        <div id="comb">
        </div>
    </div>
</section>

<script src="../public/js/product.js"></script>




















