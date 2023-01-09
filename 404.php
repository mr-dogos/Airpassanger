<?php
$context = Timber::get_context();
$context['logo'] = getLogo();
$context['leng'] = setLeng();
$context['role'] = get_current_user_role();
$context['logout'] = wp_logout_url(home_url());
if($context['leng'] == 'ru'):
$context['tile'] = getTile(1,'ru');
$context['menu'] = new Timber\Menu('AIRRU');
else:
$context['tile'] = getTile(1,'en');
$context['menu'] = new Timber\Menu('AIREN');
endif;
$context['home'] = home_url();
$context['post'] = new TimberPost(291);
Timber::render('template/404.twig', $context);