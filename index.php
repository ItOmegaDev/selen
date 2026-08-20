<?php
require __DIR__ . '/lib.php';
$items = products();
$cats = [];
foreach ($items as $p) $cats[$p['category'] ?? 'Хімічні реактиви'] = true;
ksort($cats);
?><!doctype html>
<html lang="uk">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Торговий дім «Селен» — каталог</title>
<meta name="description" content="Хімічні реактиви, сировина і лабораторні матеріали. Пошук за назвою, формулою та CAS.">
<link rel="stylesheet" href="styles.css">
</head>
<body>
<header class="topbar">
<a class="brand" href="index.php"><span class="mark">Se</span><span>Торговий дім <b>«Селен»</b></span></a>
<nav><a href="#catalog">Каталог</a><a href="#about">Про нас</a><a href="#contact">Контакти</a></nav>
<a class="cta" href="tel:+380445762163">+380 (44) 576-21-63</a>
</header>

<main>
<section class="hero">
<div><div class="eyebrow">Хімічна продукція</div><h1>Реактиви та сировина для бізнесу й лабораторій</h1>
<p>Актуальний каталог із пошуком українською, російською, за хімічною формулою та CAS.</p>
<a class="primary" href="#catalog">Відкрити каталог</a></div>
<div class="hero-number"><b><?=count($items)?></b><span>позицій у каталозі</span></div>
</section>

<section class="section" id="catalog">
<div class="section-head"><div><div class="eyebrow">Каталог</div><h2>Усі товари</h2></div>
<p>Ціни та наявність можна змінювати через захищену адмін-панель.</p></div>
<div class="catalog-tools">
<div class="search-wrap"><input id="search" type="search" autocomplete="off" placeholder="Назва, формула або CAS…"><div id="suggestions" class="suggestions"></div></div>
<select id="category"><option value="">Усі категорії</option><?php foreach($cats as $c=>$_): ?><option><?=esc($c)?></option><?php endforeach; ?></select>
<select id="status"><option value="">Будь-який статус</option><option>В наявності</option><option>Під замовлення</option><option>Недоступний</option></select>
</div>

<div id="products" class="products">
<?php foreach($items as $p): ?>
<article class="product-card"
 data-name="<?=esc(mb_strtolower($p['name'] ?? '','UTF-8'))?>"
 data-cat="<?=esc($p['category'] ?? '')?>"
 data-status="<?=esc($p['status'] ?? '')?>"
 data-formula="<?=esc($p['formula'] ?? '')?>"
 data-cas="<?=esc($p['cas'] ?? '')?>">
<a class="product-visual" href="product.php?slug=<?=urlencode($p['slug'])?>">
<?php if(!empty($p['image'])): ?><img src="<?=esc($p['image'])?>" alt="<?=esc($p['name'])?>"><?php else: ?><span>Se</span><?php endif; ?>
</a>
<div class="product-meta"><span class="category"><?=esc($p['category'] ?? '')?></span><span class="stock"><?=esc($p['status'] ?? '')?></span></div>
<a href="product.php?slug=<?=urlencode($p['slug'])?>"><h3><?=esc($p['name'])?></h3></a>
<div class="formula-mini">Формула: <?=esc($p['formula'] ?? '—')?></div>
<div class="cas-mini">CAS: <?=esc($p['cas'] ?? '—')?></div>
<?php $packs = is_array($p['packaging'] ?? null) ? $p['packaging'] : []; ?>
<?php if($packs): ?><div class="pack-mini">Фасування: <?=esc(implode(' · ', array_map(fn($x)=>(string)($x['label']??''), $packs)))?></div><?php endif; ?>
<div class="product-bottom"><strong><?=esc($p['price'] ?? '')?></strong><a class="small-btn" href="product.php?slug=<?=urlencode($p['slug'])?>">Детальніше</a></div>
</article>
<?php endforeach; ?>
</div>
<div id="empty" class="empty">Нічого не знайдено.</div>
</section>

<section class="about section" id="about"><div><div class="eyebrow">Про компанію</div><h2>ПП «Торговий дім Селен»</h2></div>
<div><p>Постачання хімічних реактивів, промислової, харчової та фармацевтичної сировини, лабораторних матеріалів.</p><p>Код ЄДРПОУ: <b>34750696</b>.</p></div></section>

<section class="contact section" id="contact"><div><div class="eyebrow">Контакти</div><h2>Зв’яжіться з нами</h2>
<p>вул. Промислова, 2, 02088, Київ</p><p><a href="tel:+380445762163">+380 (44) 576-21-63</a> · <a href="tel:+380667139228">+380 (66) 713-92-28</a></p>
<p><a href="mailto:selen_office@ukr.net">selen_office@ukr.net</a></p></div></section>
</main>

<section class="map-section" id="map">
  <div class="map-copy">
    <div class="map-kicker">ЯК НАС ЗНАЙТИ</div>
    <h2>Торговий дім «Селен»</h2>
    <div class="address-badge">📍 Київ, Бортничі, вул. Промислова, 2</div>
    <p>Перед виїздом рекомендуємо узгодити час відвідування телефоном.</p>

    <div class="directions">
      <div class="direction-card">
        <div class="direction-icon">🚗</div>
        <div>
          <h3>Автомобілем</h3>
          <p>У навігаторі введіть: <strong>Київ, Бортничі, вул. Промислова, 2</strong>. Кнопка нижче відкриє маршрут у Google Maps.</p>
        </div>
      </div>
      <div class="direction-card">
        <div class="direction-icon">🚌</div>
        <div>
          <h3>Громадським транспортом</h3>
          <p>Орієнтир — мікрорайон <strong>Бортничі</strong>. Є сполучення від станцій метро <strong>«Харківська»</strong> та <strong>«Бориспільська»</strong>; один із маршрутів — автобус №104. Актуальний маршрут і пересадки краще перевірити перед виїздом у сервісі міського транспорту.</p>
        </div>
      </div>
    </div>

    <div class="map-actions">
      <a class="map-route-btn" href="https://www.google.com/maps/dir/?api=1&destination=%D0%9A%D0%B8%D1%97%D0%B2%2C%20%D0%91%D0%BE%D1%80%D1%82%D0%BD%D0%B8%D1%87%D1%96%2C%20%D0%B2%D1%83%D0%BB.%20%D0%9F%D1%80%D0%BE%D0%BC%D0%B8%D1%81%D0%BB%D0%BE%D0%B2%D0%B0%2C%202" target="_blank" rel="noopener">Прокласти маршрут</a>
      <a class="map-phone-btn" href="tel:+380445762163">Зателефонувати</a>
    </div>
  </div>
  <div class="map-frame map-labeled">
    <div class="map-label"><strong>Торговий дім «Селен»</strong><span>вул. Промислова, 2 · Бортничі</span></div>
    <iframe
      title="Карта проїзду — Торговий дім Селен, Київ, Бортничі, вул. Промислова, 2"
      src="https://www.google.com/maps?q=%D0%9A%D0%B8%D1%97%D0%B2%2C%20%D0%91%D0%BE%D1%80%D1%82%D0%BD%D0%B8%D1%87%D1%96%2C%20%D0%B2%D1%83%D0%BB.%20%D0%9F%D1%80%D0%BE%D0%BC%D0%B8%D1%81%D0%BB%D0%BE%D0%B2%D0%B0%2C%202&output=embed"
      loading="lazy"
      referrerpolicy="no-referrer-when-downgrade"
      allowfullscreen>
    </iframe>
  </div>
</section>
<footer><div class="brand"><span class="mark">Se</span><span>Торговий дім <b>«Селен»</b></span></div><p>© 2026 ПП «Торговий дім Селен»</p><a href="admin/">Адмін</a></footer>
<script src="script.js"></script>
</body></html>