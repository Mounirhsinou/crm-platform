<?php
/**
 * Date Filter Helper
 * Manages global date range selection and calculations
 */

class DateFilter
{
    /**
     * Get the current date range from session or defaults
     * 
     * @return array [start, end, label]
     */
    public static function getRange()
    {
        $filter = Session::get('global_date_filter', 'all');
        $start = null;
        $end = null;
        $label = 'All Time';

        switch ($filter) {
            case 'today':
                $start = date('Y-m-d 00:00:00');
                $end = date('Y-m-d 23:59:59');
                $label = 'Today';
                break;
            case 'this_week':
                $start = date('Y-m-d 00:00:00', strtotime('monday this week'));
                $end = date('Y-m-d 23:59:59', strtotime('sunday this week'));
                $label = 'This Week';
                break;
            case 'this_month':
                $start = date('Y-m-01 00:00:00');
                $end = date('Y-m-t 23:59:59');
                $label = 'This Month';
                break;
            case 'custom':
                $start = Session::get('global_date_start');
                $end = Session::get('global_date_end');
                $label = 'Custom Range';
                if ($start)
                    $start .= ' 00:00:00';
                if ($end)
                    $end .= ' 23:59:59';
                break;
            case 'all':
            default:
                $start = null;
                $end = null;
                $label = 'All Time';
                break;
        }

        return [
            'filter' => $filter,
            'start' => $start,
            'end' => $end,
            'label' => $label
        ];
    }

    /**
     * Update the global filter
     * 
     * @param string $filter
     * @param string $start
     * @param string $end
     */
    public static function setFilter($filter, $start = null, $end = null)
    {
        Session::set('global_date_filter', $filter);
        if ($filter === 'custom') {
            Session::set('global_date_start', $start);
            Session::set('global_date_end', $end);
        } else {
            Session::remove('global_date_start');
            Session::remove('global_date_end');
        }
    }

    /**
     * Apply date range to SQL conditions
     * 
     * @param string $column Column name (e.g. created_at)
     * @param string &$sql SQL string to append to
     * @param array &$params Parameters array for PDO
     */
    public static function applyToSql($column, &$sql, &$params)
    {
        $range = self::getRange();

        if ($range['start'] && $range['end']) {
            $sql .= " AND {$column} BETWEEN ? AND ?";
            $params[] = $range['start'];
            $params[] = $range['end'];
        } elseif ($range['start']) {
            $sql .= " AND {$column} >= ?";
            $params[] = $range['start'];
        } elseif ($range['end']) {
            $sql .= " AND {$column} <= ?";
            $params[] = $range['end'];
        }
    }

    /**
     * Render the global date filter bar
     */
    public static function render()
    {
        try {
            $range = self::getRange();
        } catch (Exception $e) {
            error_log("DateFilter Error: " . $e->getMessage());
            return; // Exit silently if something is wrong with session/config
        }
        $filter = $range['filter'] ?? 'all';
        $label = $range['label'] ?? 'All Time';

        // Load custom dates for the modal
        $customStart = Session::get('global_date_start', '');
        $customEnd = Session::get('global_date_end', '');

        ?>
        <div class="card border-0 shadow-sm mb-4 global-date-filter-bar">
            <div class="card-body py-2">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-card-icon bg-primary bg-opacity-10 text-primary"
                            style="width: 32px; height: 32px; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; border-radius: 8px;">
                            <i class="bi bi-calendar3"></i>
                        </div>
                        <div>
                            <span class="text-muted small fw-medium d-block" style="line-height: 1;">Global Period</span>
                            <span class="text-main fw-bold small"><?php echo $label; ?></span>
                        </div>
                    </div>

                    <div class="nav nav-pills bg-light p-1 rounded-pill small">
                        <form action="<?php echo APP_URL; ?>/filter/set" method="POST" id="globalDateFilterForm"
                            class="d-flex gap-1">
                            <input type="hidden" name="filter" id="globalFilterInput">

                            <button type="button" onclick="setGlobalFilter('all')"
                                class="nav-link py-1 px-3 rounded-pill <?php echo $filter === 'all' ? 'active shadow-sm' : 'text-secondary'; ?>">All
                                Time</button>

                            <button type="button" onclick="setGlobalFilter('today')"
                                class="nav-link py-1 px-3 rounded-pill <?php echo $filter === 'today' ? 'active shadow-sm' : 'text-secondary'; ?>">Today</button>

                            <button type="button" onclick="setGlobalFilter('this_week')"
                                class="nav-link py-1 px-3 rounded-pill <?php echo $filter === 'this_week' ? 'active shadow-sm' : 'text-secondary'; ?>">This
                                Week</button>

                            <button type="button" onclick="setGlobalFilter('this_month')"
                                class="nav-link py-1 px-3 rounded-pill <?php echo $filter === 'this_month' ? 'active shadow-sm' : 'text-secondary'; ?>">This
                                Month</button>

                            <button type="button" data-bs-toggle="modal" data-bs-target="#customDateModal"
                                class="nav-link py-1 px-3 rounded-pill <?php echo $filter === 'custom' ? 'active shadow-sm' : 'text-secondary'; ?>">Custom</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Custom Date Modal -->
        <div class="modal fade" id="customDateModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                    <div class="modal-header border-0 pb-0">
                        <h6 class="modal-title fw-bold">Custom Range</h6>
                        <button type="button" class="btn-close small" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="<?php echo APP_URL; ?>/filter/set" method="POST">
                        <input type="hidden" name="filter" value="custom">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label small text-muted">Start Date</label>
                                <input type="date" name="start_date" class="form-control form-control-sm"
                                    value="<?php echo $customStart; ?>" required>
                            </div>
                            <div class="mb-0">
                                <label class="form-label small text-muted">End Date</label>
                                <input type="date" name="end_date" class="form-control form-control-sm"
                                    value="<?php echo $customEnd; ?>" required>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="submit" class="btn btn-primary btn-sm w-100 py-2">Apply Filter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            function setGlobalFilter(type) {
                document.getElementById('globalFilterInput').value = type;
                document.getElementById('globalDateFilterForm').submit();
            }
        </script>
        <?php
    }
}
