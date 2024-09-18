<?php

namespace classes;

class Pagination
{
    public int $current_page;
    public int $per_page;
    public int $total_count;

    public function __construct(int $current_page = 1, int $per_page=10, int $total_pages=0)
    {
        $this->current_page = $current_page;
        $this->per_page = $per_page;
        $this->total_count = $total_pages;
    }

    public function offset() : int {
        return ($this->current_page - 1) * $this->per_page;
    }

    public function total_pages() : float {
        return ceil($this->total_count / $this->per_page);
    }

    public function next_page() :int {
        $next = $this->current_page + 1;
        return ($next <= $this->total_pages()) ? $next : -1;
    }

    public function previous_page() :int {
        $previous = $this->current_page - 1;
        return ($previous > 0) ? $previous : -1;
    }

    public function display($base_url) : string {
        ob_start();
        ?>

        <?php if ($this->total_count > 1): ?>
            <div id="pagination">
                <?php if ($this->previous_page() != -1): ?>
                    <a href="<?= $base_url ?>?page=<?= $this->previous_page() ?>">Previous Page</a>
                <?php endif; ?>

                <?php if ($this->next_page() != -1): ?>
                    <a href="<?= $base_url ?>?page=<?= $this->next_page() ?>">Next Page</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php
        return ob_get_clean();
    }

}