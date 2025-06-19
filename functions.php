<?php
if (!function_exists('build_menu')) {
    function build_menu($items, $parent_id = 0, $level = 0) {
        if (!isset($items[$parent_id])) return '';
        $html = '<ul class="navbar-nav' . ($level > 0 ? ' dropdown-menu' : '') . '">';
        foreach ($items[$parent_id] as $item) {
            $slug = $item['slug_vi'] ?: $item['option_name']; // Sử dụng option_name nếu slug_vi trống
            $html .= '<li class="nav-item' . (isset($items[$item['id']]) ? ' dropdown' : '') . '">';
            $html .= '<a class="nav-link' . (isset($items[$item['id']]) ? ' dropdown-toggle' : '') . '" href="/' . htmlspecialchars($slug) . '"' . (isset($items[$item['id']]) ? ' data-toggle="dropdown"' : '') . '>' . htmlspecialchars($item['title_vi']) . '</a>';
            $html .= build_menu($items, $item['id'], $level + 1);
            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }
}