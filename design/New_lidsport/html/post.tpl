<div class="shares">
	<div class="shares__wrapper wrapper">

		<div class="navigation">
			<div class="navigation__wrapper">
				<h1 data-post="{$post->id}" class="navigation__title">{$post->name|escape}</h1>
				<nav class="navigation__list">
					<a href="/" class="navigation__item">Главная</a>
					- <a href="/stock" class="navigation__item">Акции</a>
					- <span class="navigation__item">{$post->name|escape}</span>
				</nav>
			</div>
		</div>

		<div class="shares__content">
			{$post->text}
		</div>

		{if $post->video}
			<br><iframe width="560" height="315" src="http://www.youtube.com/embed/{$post->video}?rel=0" frameborder="0" allowfullscreen></iframe>
		{/if}

		<a href="/stock" class="shares__back">Назад</a>

		<!-- Соседние записи /-->
		<!--<div id="back_forward">
			{if $prev_post}
			←&nbsp;<a class="prev_page_link" href="/stock/{$prev_post->url}" style="margin-right:50px;">{$prev_post->name}</a>
			{/if}
			{if $next_post}
			<a class="next_page_link" href="/stock/{$next_post->url}">{$next_post->name}</a>&nbsp;→
			{/if}
		</div> -->

	</div>
</div>