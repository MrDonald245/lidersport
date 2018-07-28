{* Постраничный вывод *}

{if $total_pages_num>1}        

<!-- Листалка страниц -->
<div class="pagination">
	<p class="pagination__title">Страницы:</p>
        <ul class="pagination__list">
	{* Количество выводимых ссылок на страницы *}
	{$visible_pages = 11}

	{* По умолчанию начинаем вывод со страницы 1 *}
	{$page_from = 1}
	
	{* Если выбранная пользователем страница дальше середины "окна" - начинаем вывод уже не с первой *}
	{if $current_page_num > floor($visible_pages/2)}
		{$page_from = max(1, $current_page_num-floor($visible_pages/2)-1)}
	{/if}	
	
	{* Если выбранная пользователем страница близка к концу навигации - начинаем с "конца-окно" *}
	{if $current_page_num > $total_pages_num-ceil($visible_pages/2)}
		{$page_from = max(1, $total_pages_num-$visible_pages-1)}
	{/if}
	
	{* До какой страницы выводить - выводим всё окно, но не более ощего количества страниц *}
	{$page_to = min($page_from+$visible_pages, $total_pages_num-1)}

	{* Ссылка на 1 страницу отображается всегда *}
        <li class="pagination__item">
	<a {if $current_page_num==1}class="selected pagination__link"{/if} style="text-decoration: none; color: #333" href="{url page=null}">1</a>
	 </li>
         <li class="pagination__item">
	{* Выводим страницы нашего "окна" *}	
	{section name=pages loop=$page_to start=$page_from}
		{* Номер текущей выводимой страницы *}	
		{$p = $smarty.section.pages.index+1}	
		{* Для крайних страниц "окна" выводим троеточие, если окно не возле границы навигации *}
                
		{if ($p == $page_from+1 && $p!=2) || ($p == $page_to && $p != $total_pages_num-1)}
                
		<a {if $p==$current_page_num}class="selected pagination__link"{/if}  style="text-decoration: none; color: #333" href="{url page=$p}">...</a>
                </li>
                
		
                <li class="pagination__item">
                {else}
		<a {if $p==$current_page_num}class="selected active pagination__link"{/if} style="text-decoration: none; color: #333" href="{url page=$p}">{$p}</a>
                {/if}
                </li>
		
	{/section}
	<li class="pagination__item">
	{* Ссылка на последнююю страницу отображается всегда *}
	<a {if $current_page_num==$total_pages_num}class="selected active pagination__link"{/if}  style="text-decoration: none; color: #333" href="{url page=$total_pages_num}">{$total_pages_num}</a>
	</li>
        <li class="pagination__item">
	
        <a href="{url page=all}" style="text-decoration: none; color: #333"> все сразу </a>
        </li>
        <li class="pagination__item">
	{if $current_page_num==2}<a class="prev_page_link pagination__link" href="{url page=null}"> &#8249; назад </a>{/if}
	</li>
        <li class="pagination__item">
        {if $current_page_num>2}<a class="prev_page_link pagination__link" href="{url page=$current_page_num-1}"> &#8249; назад </a>{/if}
	</li>
        <li class="pagination__item">
        {if $current_page_num<$total_pages_num}<a class="next_page_link pagination__link" href="{url page=$current_page_num+1}"> вперед › </a>{/if}
	</li>
         </ul>
</div>
<!-- Листалка страниц (The End) -->
{/if}
