<?php //$qs = isset($qs) ? "&".$qs:''?>
<?php if ($pager->haveToPaginate()): ?>
<div class="<?php echo !isset($leftAlign) ? 'pull-right':'';?>">
      <ul class="pagination" <?php echo isset($leftAlign) ? 'style="margin-top: 0px !important;"':'';?>>
        <?php $queryString = isset($searchBy) && $searchBy != "" ? '&search_field=' . $searchBy : ''; ?>
        <?php $queryString .= isset($searchByVal) && $searchByVal != "" ? '&search_text=' . $searchByVal : ''; ?>
        <?php $queryString .= isset($sortBy) && $sortBy != "" ? '&sortBy=' . $sortBy : ''; ?>
        <?php $queryString .= isset($sortOrd) && $sortOrd != "" ? '&sortOrd=' . $sortOrd : ''; ?>
        <?php $queryString .= isset($qs) && $qs != "" ? '&' . html_entity_decode($qs) : ''; ?>

        <li <?php echo ($page <= 1) ? 'class="disabled"' : '' ?> >
          <?php echo link_to('Prev', $pagingLink . '?page=' . $pager->getPreviousPage() . $queryString, array('id' => $pager->getPreviousPage())) ?>
        </li>
        <?php $links = $pager->getLinks(); ?>
        <?php foreach ($links as $p): ?>
          <li <?php echo ($p == $pager->getPage()) ? 'class="active"' : '' ?> >
            <?php echo ($p == $pager->getPage()) ? link_to($p, $pagingLink . '?page=' . $p . $queryString, array('id' => $p)) : link_to($p, $pagingLink . '?page=' . $p . $queryString, array('id' => $p)); ?>
          </li>
        <?php endforeach ?>
        <li <?php echo ($page >= count($links)) ? 'class="disabled"' : '' ?>>
          <?php echo link_to('Next', $pagingLink . '?page=' . $pager->getNextPage() . $queryString, array('id' => $pager->getNextPage())) ?>
        </li>
      </ul>
    
  </div>
<?php
endif; // # $pager->haveToPaginate()  ?>