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

    public function next_page() : int {
        $next = $this->current_page + 1;
        return ($next <= $this->total_pages()) ? $next : -1;
    }

    public function previous_page() : int {
        $previous = $this->current_page - 1;
        return ($previous > 0) ? $previous : -1;
    }

    public function display() : string {
        $full_link = self::get_protocol() . "$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $init_link = self::get_protocol() . parse_url($full_link, PHP_URL_HOST) . ':'. parse_url($full_link, PHP_URL_PORT) . parse_url($full_link, PHP_URL_PATH);

        $queries_str = parse_url($full_link, PHP_URL_QUERY);
        $queries = array();

        // Split the queries to an associative array
        if (!empty($queries_str)) {
            parse_str($queries_str, $queries);
        }

        // Builds out the query based on valid information
        $next = $this->next_page();
        $prev = $this->previous_page();
        $next_class = '';
        $prev_class = '';
        if ($next !== -1) {
            $queries['page'] = $next;
        } else {
            $next_class = 'disabled';
        }
        $query_string = http_build_query($queries);
        $next_page_link = $init_link . '?' . $query_string;

        if ($prev !== -1) {
            $queries['page'] = $prev;
        } else {
            // Same page that user is on
            $queries['page'] = $next - 1;
            $prev_class = 'disabled';
        }
        $query_string = http_build_query($queries);
        $prev_page_link = $init_link . '?' . $query_string;

        ob_start();
        ?>

        <?php if ($this->total_count > 1): ?>
            <div id="pagination">
                <a class="<?php echo $prev_class ?>" href="<?= $prev_page_link ?>">Previous Page</a>
                <a class="<?php echo $next_class ?>" href="<?= $next_page_link ?>">Next Page</a>
            </div>
        <?php endif; ?>

        <?php
        return ob_get_clean();
    }

    private function get_protocol() : string {
        if (isset($_SERVER['HTTPS']) &&
            ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
            isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
            $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $protocol = 'https://';
        }
        else {
            $protocol = 'http://';
        }
        return $protocol;
    }

}