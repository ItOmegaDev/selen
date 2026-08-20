<?php
require dirname(__DIR__) . '/lib.php';
$msg=''; $err='';
$admin = load_json(ADMIN_FILE);

if (!is_admin() && $_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['login'])) {
    $u=(string)($_POST['username']??''); $p=(string)($_POST['password']??'');
    if (hash_equals((string)($admin['username']??''),$u) && password_verify($p,(string)($admin['password_hash']??''))) {
        session_regenerate_id(true); $_SESSION['selen_admin']=true; header('Location: index.php'); exit;
    } else $err='Невірний логін або пароль.';
}
if (!is_admin()) {
?><!doctype html><html lang="uk"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Адмін — Селен</title><link rel="stylesheet" href="../styles.css"></head>
<body class="admin-body"><div class="admin-shell"><div class="admin-card admin-login"><div class="brand"><span class="mark">Se</span><span>Адмін-панель</span></div><h1>Вхід</h1>
<?php if($err):?><div class="notice error"><?=esc($err)?></div><?php endif;?>
<form method="post" class="admin-form"><input type="hidden" name="login" value="1"><input name="username" required placeholder="Логін"><input type="password" name="password" required placeholder="Пароль"><button class="button">Увійти</button></form></div></div></body></html><?php exit; }

if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
    $items=products();
    $action=(string)($_POST['action']??'');
    if ($action==='save') {
        $slug=(string)($_POST['slug']??'');
        foreach($items as &$p) if(($p['slug']??'')===$slug){
            foreach(['name','category','status','price','formula','cas','image','description'] as $k) $p[$k]=trim((string)($_POST[$k]??''));
            $labels = $_POST['pack_label'] ?? [];
            $prices = $_POST['pack_price'] ?? [];
            $packs = [];
            if (is_array($labels)) {
                foreach($labels as $i=>$lab){
                    $lab = trim((string)$lab);
                    $pr = trim((string)($prices[$i] ?? ''));
                    if($lab !== '') $packs[] = ['label'=>$lab,'price'=>$pr];
                }
            }
            $p['packaging'] = $packs;
            break;
        } unset($p);
        if(save_json(PRODUCTS_FILE,$items)) $msg='Товар збережено.'; else $err='Не вдалося записати файл каталогу.';
    } elseif($action==='add') {
        $name=trim((string)($_POST['name']??'')); if($name==='') $err='Вкажіть назву.';
        else {
            $slug=slugify($name); $used=array_column($items,'slug'); $baseSlug=$slug; $i=2; while(in_array($slug,$used,true)){$slug=$baseSlug.'-'.$i++;}
            $labels=$_POST['pack_label']??[]; $prices=$_POST['pack_price']??[]; $packs=[];
            if(is_array($labels)){foreach($labels as $idx=>$lab){$lab=trim((string)$lab);$pr=trim((string)($prices[$idx]??''));if($lab!=='')$packs[]=['label'=>$lab,'price'=>$pr];}}
            $items[]=[
              'slug'=>$slug,'name'=>$name,
              'category'=>trim((string)($_POST['category']??'Хімічні реактиви')),
              'status'=>trim((string)($_POST['status']??'В наявності')),
              'price'=>trim((string)($_POST['price']??'')),
              'formula'=>trim((string)($_POST['formula']??'')),
              'cas'=>trim((string)($_POST['cas']??'')),
              'image'=>trim((string)($_POST['image']??'')),
              'description'=>trim((string)($_POST['description']??'')),
              'packaging'=>$packs
            ];
            if(save_json(PRODUCTS_FILE,$items)) $msg='Товар додано.'; else $err='Не вдалося додати товар.';
        }
    } elseif($action==='delete') {
        $slug=(string)($_POST['slug']??''); $items=array_values(array_filter($items,fn($p)=>($p['slug']??'')!==$slug));
        if(save_json(PRODUCTS_FILE,$items)) $msg='Товар видалено.'; else $err='Не вдалося видалити товар.';
    } elseif($action==='duplicate') {
        $slug=(string)($_POST['slug']??'');
        foreach($items as $p){
            if(($p['slug']??'')===$slug){
                $copy=$p; $copy['name']=($p['name']??'').' — копія';
                $newSlug=slugify($copy['name']); $used=array_column($items,'slug'); $baseSlug=$newSlug; $i=2;
                while(in_array($newSlug,$used,true)){$newSlug=$baseSlug.'-'.$i++;}
                $copy['slug']=$newSlug; $items[]=$copy; break;
            }
        }
        if(save_json(PRODUCTS_FILE,$items)) $msg='Товар продубльовано.'; else $err='Не вдалося продублювати товар.';
    } elseif($action==='password') {
        $old=(string)($_POST['old_password']??''); $new=(string)($_POST['new_password']??'');
        if(!password_verify($old,(string)($admin['password_hash']??''))) $err='Поточний пароль невірний.';
        elseif(strlen($new)<10) $err='Новий пароль має містити щонайменше 10 символів.';
        else {$admin['password_hash']=password_hash($new,PASSWORD_DEFAULT); if(save_json(ADMIN_FILE,$admin))$msg='Пароль змінено.';else$err='Не вдалося змінити пароль.';}
    }
}
$items=products(); $edit=(string)($_GET['edit']??''); $editing=null;
foreach($items as $p) if(($p['slug']??'')===$edit){$editing=$p;break;}
?><!doctype html><html lang="uk"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Адмін — Селен</title><link rel="stylesheet" href="../styles.css"></head>
<body class="admin-body"><div class="admin-shell">
<div class="admin-head"><div><div class="eyebrow">Панель керування</div><h1>Каталог — <?=count($items)?> товарів</h1></div><div class="admin-actions"><a class="button light" href="../index.php" target="_blank">Відкрити сайт</a><a class="button" href="logout.php">Вийти</a></div></div>
<?php if($msg):?><div class="notice"><?=esc($msg)?></div><?php endif;?><?php if($err):?><div class="notice error"><?=esc($err)?></div><?php endif;?>

<?php if($editing): ?>
<div class="admin-card"><h2>Редагування товару</h2><form method="post" class="admin-form"><input type="hidden" name="csrf" value="<?=esc(csrf_token())?>"><input type="hidden" name="action" value="save"><input type="hidden" name="slug" value="<?=esc($editing['slug'])?>">
<div class="edit-grid"><label>Назва<input name="name" value="<?=esc($editing['name'])?>"></label><label>Категорія<input name="category" value="<?=esc($editing['category'])?>"></label>
<label>Ціна<input name="price" value="<?=esc($editing['price'])?>"></label><label>Статус<select name="status"><?php foreach(['В наявності','Під замовлення','Недоступний'] as $s):?><option <?=$editing['status']===$s?'selected':''?>><?=esc($s)?></option><?php endforeach;?></select></label>
<label>Формула<input name="formula" value="<?=esc($editing['formula'])?>"></label><label>CAS<input name="cas" value="<?=esc($editing['cas'])?>"></label>
<label class="full">URL фото<input name="image" value="<?=esc($editing['image']??'')?>"></label>
<label class="full">Опис<textarea name="description" rows="5"><?=esc($editing['description']??'')?></textarea></label>
<div class="full">
  <h3>Фасування і ціни</h3>
  <p class="admin-muted">Можна змінювати назву фасування, ціну, додавати нові рядки або видаляти непотрібні.</p>
  <div id="packs">
  <?php $packs=is_array($editing['packaging']??null)?$editing['packaging']:[]; foreach($packs as $pk): ?>
    <div class="pack-admin-row">
      <div><span class="pack-admin-caption">Фасування</span><input name="pack_label[]" value="<?=esc($pk['label']??'')?>" placeholder="Напр. 1 кг"></div>
      <div><span class="pack-admin-caption">Ціна, грн</span><input name="pack_price[]" value="<?=esc($pk['price']??'')?>" placeholder="Ціну уточнюйте"></div>
      <button type="button" class="button danger remove-pack">Видалити</button>
    </div>
  <?php endforeach; ?>
  </div>
  <button type="button" class="button light" id="addPack">+ Додати фасування</button>
</div></div>
<div class="admin-actions"><button class="button">Зберегти</button><a class="button light" href="index.php">Скасувати</a></div></form></div>
<script>
const packs=document.getElementById('packs');
document.getElementById('addPack')?.addEventListener('click',()=>{
  const d=document.createElement('div'); d.className='pack-admin-row';
  d.innerHTML='<div><span class="pack-admin-caption">Фасування</span><input name="pack_label[]" placeholder="Напр. 10 кг"></div><div><span class="pack-admin-caption">Ціна, грн</span><input name="pack_price[]" placeholder="Ціну уточнюйте"></div><button type="button" class="button danger remove-pack">Видалити</button>';
  packs.appendChild(d);
});
document.addEventListener('click',e=>{if(e.target.classList.contains('remove-pack'))e.target.closest('.pack-admin-row')?.remove()});
</script>
<?php else: ?>
<div class="admin-card">
<h2>Додати новий товар</h2>
<form method="post" class="admin-form">
<input type="hidden" name="csrf" value="<?=esc(csrf_token())?>">
<input type="hidden" name="action" value="add">
<div class="edit-grid">
<label>Назва<input name="name" required placeholder="Назва товару"></label>
<label>Категорія<input name="category" value="Хімічні реактиви"></label>
<label>Основна ціна<input name="price" placeholder="Напр. 250 ₴/кг"></label>
<label>Статус<select name="status"><option>В наявності</option><option>Під замовлення</option><option>Недоступний</option></select></label>
<label>Формула<input name="formula" placeholder="NaOH"></label>
<label>CAS<input name="cas" placeholder="1310-73-2"></label>
<label class="full">URL фото<input name="image" placeholder="https://..."></label>
<label class="full">Опис<textarea name="description" rows="4" placeholder="Опис товару"></textarea></label>
<div class="full"><h3>Фасування і ціни</h3><div id="newPacks">
<div class="pack-admin-row"><div><span class="pack-admin-caption">Фасування</span><input name="pack_label[]" value="0,1 кг"></div><div><span class="pack-admin-caption">Ціна, грн</span><input name="pack_price[]" placeholder="Ціну уточнюйте"></div><button type="button" class="button danger remove-new-pack">Видалити</button></div>
<div class="pack-admin-row"><div><span class="pack-admin-caption">Фасування</span><input name="pack_label[]" value="1 кг"></div><div><span class="pack-admin-caption">Ціна, грн</span><input name="pack_price[]" placeholder="Ціна"></div><button type="button" class="button danger remove-new-pack">Видалити</button></div>
</div><button type="button" class="button light" id="addNewPack">+ Додати фасування</button></div>
</div><div class="admin-actions"><button class="button">Створити товар</button></div></form>
</div>
<script>
const newPacks=document.getElementById('newPacks');
document.getElementById('addNewPack')?.addEventListener('click',()=>{const d=document.createElement('div');d.className='pack-admin-row';d.innerHTML='<div><span class="pack-admin-caption">Фасування</span><input name="pack_label[]" placeholder="Напр. 5 кг"></div><div><span class="pack-admin-caption">Ціна, грн</span><input name="pack_price[]" placeholder="Ціну уточнюйте"></div><button type="button" class="button danger remove-new-pack">Видалити</button>';newPacks.appendChild(d)});
document.addEventListener('click',e=>{if(e.target.classList.contains('remove-new-pack'))e.target.closest('.pack-admin-row')?.remove()});
</script>
<div class="admin-card" style="margin-top:18px"><input id="adminSearch" class="admin-search-input" style="width:100%;padding:13px;border:1px solid #dce6df;border-radius:10px" placeholder="Пошук товару в адмінці…">
<table class="admin-table"><thead><tr><th>Назва</th><th>Ціна</th><th>Статус</th><th>Формула / CAS</th><th></th></tr></thead><tbody id="adminRows">
<?php foreach($items as $p): ?><tr data-text="<?=esc(mb_strtolower(($p['name']??'').' '.($p['formula']??'').' '.($p['cas']??''),'UTF-8'))?>"><td><b><?=esc($p['name'])?></b><div class="admin-muted"><?=esc($p['category'])?></div></td><td><?=esc($p['price'])?></td><td><?=esc($p['status'])?></td><td><?=esc($p['formula'])?><br><span class="admin-muted"><?=esc($p['cas'])?></span></td><td><div class="admin-actions"><a class="button light" href="?edit=<?=urlencode($p['slug'])?>">Редагувати</a>
<form method="post"><input type="hidden" name="csrf" value="<?=esc(csrf_token())?>"><input type="hidden" name="action" value="duplicate"><input type="hidden" name="slug" value="<?=esc($p['slug'])?>"><button class="button light">Копія</button></form>
<form method="post" onsubmit="return confirm('Видалити цей товар?')"><input type="hidden" name="csrf" value="<?=esc(csrf_token())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="slug" value="<?=esc($p['slug'])?>"><button class="button danger">Видалити</button></form></div></td></tr><?php endforeach;?>
</tbody></table></div>
<div class="admin-card" style="margin-top:18px"><h2>Змінити пароль адміністратора</h2><form method="post" class="admin-form"><input type="hidden" name="csrf" value="<?=esc(csrf_token())?>"><input type="hidden" name="action" value="password"><input type="password" name="old_password" required placeholder="Поточний пароль"><input type="password" name="new_password" required placeholder="Новий пароль (мін. 10 символів)"><button class="button">Змінити пароль</button></form></div>
<script>adminSearch.addEventListener('input',()=>{const q=adminSearch.value.toLowerCase();document.querySelectorAll('#adminRows tr').forEach(r=>r.style.display=r.dataset.text.includes(q)?'':'none')})</script>
<?php endif;?>
</div></body></html>