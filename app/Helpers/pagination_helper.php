<?php

/**
 * Safe pagination parameters — prevents negative LIMIT offsets.
 *
 * @return array{page: int, perPage: int, offset: int}
 */
function pagination_params(?int $page = null, int $perPage = 25): array
{
    $page    = max(1, (int) ($page ?? 1));
    $perPage = max(1, $perPage);
    $offset  = ($page - 1) * $perPage;

    return [
        'page'    => $page,
        'perPage' => $perPage,
        'offset'  => $offset,
    ];
}


if (! function_exists('paginate')) {
    /**
     * Render simple pagination links for list views.
     */
    function paginate(int $total, int $perPage, int $currentPage): string
    {
        if ($perPage < 1 || $total <= $perPage) {
            return '';
        }
        $pages = (int) ceil($total / $perPage);
        $currentPage = max(1, min($currentPage, $pages));
        $html = '<nav><ul class="pagination pagination-sm mb-0">';
        for ($p = 1; $p <= $pages; $p++) {
            $active = $p === $currentPage ? ' active' : '';
            $qs = $_GET ?? [];
            $qs['page'] = $p;
            $href = '?' . http_build_query($qs);
            $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . esc($href) . '">' . $p . '</a></li>';
        }
        $html .= '</ul></nav>';
        return $html;
    }
}
