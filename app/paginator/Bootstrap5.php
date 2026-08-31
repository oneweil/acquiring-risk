<?php

declare(strict_types=1);

namespace app\paginator;

use think\paginator\driver\Bootstrap;

/**
 * Bootstrap 5 分页驱动（适配 Tabler）
 */
class Bootstrap5 extends Bootstrap
{
    public function render()
    {
        if (!$this->hasPages()) {
            return '';
        }

        if ($this->simple) {
            return sprintf(
                '<ul class="pagination pagination-sm mb-0">%s %s</ul>',
                $this->getPreviousButton('上一页'),
                $this->getNextButton('下一页')
            );
        }

        return sprintf(
            '<ul class="pagination pagination-sm mb-0">%s %s %s</ul>',
            $this->getPreviousButton('上一页'),
            $this->getLinks(),
            $this->getNextButton('下一页')
        );
    }

    protected function getAvailablePageWrapper(string $url, string $page): string
    {
        return '<li class="page-item"><a class="page-link" href="' . htmlentities($url) . '">' . $page . '</a></li>';
    }

    protected function getDisabledTextWrapper(string $text): string
    {
        return '<li class="page-item disabled"><span class="page-link">' . $text . '</span></li>';
    }

    protected function getActivePageWrapper(string $text): string
    {
        return '<li class="page-item active" aria-current="page"><span class="page-link">' . $text . '</span></li>';
    }
}
