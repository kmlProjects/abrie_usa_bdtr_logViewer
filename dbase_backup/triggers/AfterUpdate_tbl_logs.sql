CREATE DEFINER=`root`@`localhost` TRIGGER `sagps`.`tbl_logs_AFTER_UPDATE` AFTER UPDATE ON `tbl_logs` FOR EACH ROW
INSERT INTO tbl_logs_at
VALUES(
		(SELECT ifnull(max(log_at.id_logs_at),0)+1 FROM tbl_logs_at as log_at),
        old.id_logs,
        old.id_employee,
        old.id_terminal,
        old.id_attend_forced,
        old.id_attend_holiday,
        old.work_name,
        old.duty_in,
        old.duty_out,
        new.actual_in,
        new.actual_out,
        new.log_stage,
        new.min_late,
        new.min_under,
        old.pic_in,
        old.pic_out,
        new.is_voided,
        old.emp_dept,
        old.work_dept,
        old.update_log,
        old.id_work_schedule,
        old.id_void_forced,
        old.id_void_holiday,
        null,
        now(),
        null
)