<?echo $date_range_bar?>
选取范围内的工作日数：
<?echo getWorkingDays(option('date_range/from'), option('date_range/to'),getHolidays(),getOvertimedays(),false)?>