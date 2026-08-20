<?php
require __DIR__ . '/lib.php';
$slug = (string)($_GET['slug'] ?? '');
$item = null;
foreach (products() as $p) if (($p['slug'] ?? '') === $slug) { $item=$p; break; }
if (!$item) { http_response_code(404); exit('Товар не знайдено'); }
?><!doctype html><html lang="uk"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=esc($item['name'])?> — Селен</title><link rel="stylesheet" href="styles.css"></head><body>
<header class="topbar"><a class="brand" href="index.php"><span class="mark">Se</span><span>Торговий дім <b>«Селен»</b></span></a><nav><a href="index.php#catalog">Каталог</a><a href="index.php#contact">Контакти</a></nav><a class="cta" href="tel:+380445762163">Зателефонувати</a></header>
<main class="product-page"><div class="breadcrumbs"><a href="index.php">Головна</a> › <a href="index.php#catalog">Каталог</a> › <?=esc($item['name'])?></div>
<section class="product-detail">
<div class="detail-image"><?php if(!empty($item['image'])): ?><img src="<?=esc($item['image'])?>" alt="<?=esc($item['name'])?>"><?php else: ?><span>Se</span><?php endif; ?></div>
<div class="detail-info"><span class="category-pill"><?=esc($item['category'])?></span><h1><?=esc($item['name'])?></h1>
<div class="detail-status">● <?=esc($item['status'])?></div>
<?php $packs = is_array($item['packaging'] ?? null) ? $item['packaging'] : []; ?>
<?php if($packs): ?>
<div class="pack-box">
  <div class="pack-title">Оберіть фасування</div>
  <div class="pack-options" id="packOptions">
    <?php foreach($packs as $i=>$pk): 
      $hasPrice = trim((string)($pk['price'] ?? '')) !== '';
    ?>
      <button type="button" class="pack-btn <?=$i===1?'active':''?>" data-price="<?=esc($pk['price'] ?? '')?>" data-label="<?=esc($pk['label'] ?? '')?>">
        <span class="pack-label"><?=esc($pk['label'] ?? '')?></span>
        <span class="pack-price"><?=$hasPrice ? esc($pk['price']).' грн' : 'Ціну уточнюйте'?></span>
      </button>
    <?php endforeach; ?>
  </div>
  <div class="selected-pack-price">
    <span>Ціна за вибране фасування:</span>
    <strong id="selectedPackPrice">
      <?php
      $sel = '';
      if($packs){
        $candidate = $packs[1] ?? $packs[0];
        $sel = trim((string)($candidate['price'] ?? ''));
      }
      echo $sel !== '' ? esc($sel).' грн' : 'Ціну уточнюйте';
      ?>
    </strong>
  </div>
</div>
<?php endif; ?>
<div class="detail-price" id="detailPrice">
<?php
$shown = $item['price'] ?? '';
if($packs){
  foreach($packs as $pk){ if(($pk['label']??'')==='1 кг' && trim((string)($pk['price']??''))!==''){ $shown = ($pk['price']).' ₴'; break; } }
}
echo esc($shown);
?>
</div>
<p class="detail-description"><?=esc($item['description'] ?? '')?></p>
<div class="specs"><div class="spec-row"><span>Категорія</span><b><?=esc($item['category'])?></b></div>
<div class="spec-row formula-row"><span>Хімічна формула</span><b><?=esc($item['formula'])?></b></div>
<div class="spec-row cas-row"><span>CAS №</span><b><?=esc($item['cas'])?></b></div>
<div class="spec-row"><span>Статус</span><b><?=esc($item['status'])?></b></div><div class="spec-row"><span>Ціна</span><b><?=esc($item['price'])?></b></div></div>
<div class="order-box"><h3>Замовити товар</h3><form class="order-form" onsubmit="return sendOrder(event)">
<input id="n" required placeholder="Ваше ім’я"><input id="ph" required placeholder="Телефон">
<textarea id="m" rows="4">Добрий день! Мене цікавить товар: <?=esc($item['name'])?>.</textarea>
<button class="primary">Замовити</button></form></div></div>
</section></main>
<script>
const packBtns=[...document.querySelectorAll('.pack-btn')],
      priceEl=document.getElementById('detailPrice'),
      selectedPackPrice=document.getElementById('selectedPackPrice');
let selectedPack=packBtns.find(b=>b.classList.contains('active'))?.dataset.label||'';
packBtns.forEach(b=>b.addEventListener('click',()=>{
  packBtns.forEach(x=>x.classList.remove('active'));
  b.classList.add('active');
  selectedPack=b.dataset.label||'';
  const price=b.dataset.price||'';
  const text=price?price+' грн':'Ціну уточнюйте';
  if(priceEl) priceEl.textContent=text;
  if(selectedPackPrice) selectedPackPrice.textContent=text;
}));
function sendOrder(e){
  e.preventDefault();
  const b='Ім’я: '+n.value+'\nТелефон: '+ph.value+'\nФасування: '+selectedPack+'\n\n'+m.value;
  location.href='mailto:selen_office@ukr.net?subject='+encodeURIComponent('Замовлення: <?=esc($item['name'])?>')+'&body='+encodeURIComponent(b);return false
}
</script>
</body></html>