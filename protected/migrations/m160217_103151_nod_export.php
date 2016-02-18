<?php

class m160217_103151_nod_export extends CDbMigration
{
	public function up()
	{

		$storedProcedure = <<<EOL
-- Configuration settings for this script --
SET SESSION group_concat_max_len = 100000;
SET max_sp_recursion_depth = 255;

                        -- Surgeon --
                        
DROP PROCEDURE IF EXISTS get_surgeons;
CREATE DEFINER=`root`@`localhost` PROCEDURE get_surgeons(IN dir VARCHAR(255))
BEGIN
CREATE TEMPORARY TABLE tmp_doctor_grade (
	`code` INT(10) UNSIGNED NOT NULL,
	`desc` VARCHAR(100)
);

INSERT INTO tmp_doctor_grade (`code`, `desc`)
VALUES
(0, 'Consultant'),
(1, 'Locum Consultant'),
(2, 'corneal burn'),
(3, 'Associate Specialist'),
(4, 'Fellow'),
(5, 'Registrar'),
(6, 'Staff Grade'),
(7, 'Trust Doctor'),
(8, 'Senior House Officer'),
(9, 'Specialty trainee (year 1)'),
(10, 'Specialty trainee (year 2)'),
(11, 'Specialty trainee (year 3)'),
(12, 'Specialty trainee (year 4)'),
(13, 'Specialty trainee (year 5)'),
(14, 'Specialty trainee (year 6)'),
(15, 'Specialty trainee (year 7)'),
(16, 'Foundation Year 1 Doctor'),
(17, 'Foundation Year 2 Doctor'),
(18, 'GP with a special interest in ophthalmology'),
(19, 'Community ophthalmologist'),
(20, 'Anaesthetist'),
(21, 'Orthoptist'),
(22, 'Optometrist'),
(23, 'Clinical nurse specialist'),
(24, 'Nurse'),
(25, 'Health Care Assistant'),
(26, 'Ophthalmic Technician'),
(27, 'Surgical Care Practitioner'),
(28, 'Clinical Assistant'),
(29, 'RG1'),
(30, 'RG2'),
(31, 'ODP'),
(32, 'Administration staff'),
(33, 'Other');
    
SET @time_now = UNIX_TIMESTAMP(NOW());
SET @file = CONCAT(dir, '/surgeons_', @time_now, '.csv');
SET @cmd = CONCAT("(SELECT 'Surgeonid','GMCnumber','Title', 'FirstName', 'CurrentGradeId')
		UNION (SELECT id, IFNULL(registration_code, 'NULL'), IFNULL(title, 'NULL'), IFNULL(first_name, 'NULL'),
                (
			SELECT `code` 
			FROM tmp_doctor_grade, doctor_grade
			WHERE user.`doctor_grade_id` = doctor_grade.id AND doctor_grade.`grade` = tmp_doctor_grade.desc
			
		 ) AS CurrentGradeId
                FROM user 
                WHERE is_surgeon = 1 AND active = 1
                INTO OUTFILE '",@file,
		"' FIELDS ENCLOSED BY '\"' TERMINATED BY ';'",
		"  LINES TERMINATED BY '\r\n')");

PREPARE statement FROM @cmd;
EXECUTE statement;

DROP TEMPORARY TABLE tmp_doctor_grade;
                        
END;

                        -- Patient --
                        
DROP PROCEDURE IF EXISTS get_patients;
CREATE DEFINER=`root`@`localhost` PROCEDURE get_patients(IN dir VARCHAR(255))
BEGIN
SET @time_now = UNIX_TIMESTAMP(NOW());
CREATE TEMPORARY TABLE temp_patients SELECT id, gender, ethnic_group_id, dob, date_of_death FROM patient;
UPDATE temp_patients SET gender = (SELECT CASE WHEN gender='F' THEN 2 WHEN gender='M' THEN 1 ELSE 9 END);

#TODO: Add IMDScore and IsPrivate fields & confirm output type for ethnicity

SET @file = CONCAT(dir, '/patients_', @time_now, '.csv');
SET @cmd = CONCAT("(SELECT 'PatientId','GenderId','EthnicityId', 'DateOfBirth', 'DateOfDeath')
		  UNION (SELECT id, IFNULL(gender, 'NULL'), IFNULL(ethnic_group_id, 'NULL'), IFNULL(dob, 'NULL'), IFNULL(date_of_death, 'NULL') FROM temp_patients INTO OUTFILE '",@file,
		  "' FIELDS ENCLOSED BY '\"' TERMINATED BY ';'",
		  "  LINES TERMINATED BY '\r\n')");

PREPARE statement FROM @cmd;
EXECUTE statement;
DROP TEMPORARY TABLE temp_patients;

END;

                        -- PatientCVIStatus --

DROP PROCEDURE IF EXISTS get_patient_cvi_status;
CREATE DEFINER=`root`@`localhost` PROCEDURE get_patient_cvi_status(IN dir VARCHAR(255))
BEGIN
SET @time_now = UNIX_TIMESTAMP(NOW());
CREATE TEMPORARY TABLE temp_patient_cvi_status SELECT id AS PatientId, cvi_status_date AS DATE, cvi_status_id FROM patient_oph_info;
ALTER TABLE temp_patient_cvi_status ADD IsDateApprox TINYINT DEFAULT 0 NOT NULL, ADD IsCVIBlind TINYINT DEFAULT 0 NOT NULL, ADD IsCVIPartial TINYINT DEFAULT 0 NOT NULL;
UPDATE temp_patient_cvi_status SET IsCVIBlind = (SELECT CASE WHEN cvi_status_id=4 THEN 1 END),
						   IsCVIPartial = (SELECT CASE WHEN cvi_status_id=3 THEN 1 END),
						   IsDateApprox = (SELECT CASE WHEN DAYNAME(DATE) IS NULL THEN 1 END);


SET @file = CONCAT(dir, '/patient_cvi_status_', @time_now, '.csv');
SET @cmd = CONCAT("(SELECT 'PatientId', 'Date', 'IsDateApprox', 'IsCVIBlind', 'IsCVIPartial')
		  UNION (SELECT PatientId, Date, IsCVIBlind, IsCVIPartial, IsDateApprox FROM temp_patient_cvi_status INTO OUTFILE '", @file,
		  "' FIELDS ENCLOSED BY '\"' TERMINATED BY ';'",
		  "  LINES TERMINATED BY '\r\n')");

PREPARE statement FROM @cmd;
EXECUTE statement;
DROP TABLE temp_patient_cvi_status;

END;

                        -- Episode --
                        
DROP PROCEDURE IF EXISTS get_nod_episodes;
CREATE DEFINER=`root`@`localhost` PROCEDURE get_nod_episodes(IN dir VARCHAR(255))
BEGIN
SET @time_now = UNIX_TIMESTAMP(NOW());
SET @file = CONCAT(dir, '/episodes_', @time_now, '.csv');

SET @cmd = CONCAT("(SELECT 'PatientId', 'EpisodeId', 'Date')
   UNION (SELECT patient_id, id, start_date FROM episode INTO OUTFILE '",@file,
   "' FIELDS ENCLOSED BY '\"' TERMINATED BY ';'",
   "  LINES TERMINATED BY '\r\n')");

PREPARE statement FROM @cmd;
EXECUTE statement;

END;

                        -- Episode Diagnosis --
                        
DROP PROCEDURE IF EXISTS get_episodes_diagnosis;
CREATE DEFINER=`root`@`localhost` PROCEDURE get_episodes_diagnosis(IN dir VARCHAR(255))
BEGIN
SET @time_now = UNIX_TIMESTAMP(NOW());
CREATE TABLE temp_episodes_diagnosis SELECT id , firm_id, eye_id, last_modified_date ,disorder_id FROM episode;
ALTER TABLE  temp_episodes_diagnosis ADD SurgeonId INTEGER(10), ADD ConditionId INTEGER(10), ADD Eye VARCHAR(10);

SET @count = (SELECT COUNT(*) FROM temp_episodes_diagnosis);
SET @ids =  (SELECT SUBSTRING_INDEX(GROUP_CONCAT(id SEPARATOR ','), ',', @count) FROM temp_episodes_diagnosis);

WHILE (LOCATE(',', @ids) > 0) DO
SET @ids = SUBSTRING(@ids, LOCATE(',', @ids) + 1);
SET @id =  (SELECT TRIM(SUBSTRING_INDEX(@ids, ',', 1)));
SET @id = TRIM(@id);

SET @surgeon_id = (SELECT last_modified_user_id FROM episode_version WHERE id=@id ORDER BY last_modified_date ASC LIMIT 1);
SET @condition_id= (SELECT service_subspecialty_assignment_id FROM firm WHERE id = (SELECT firm_id FROM temp_episodes_diagnosis WHERE id = @id));

IF ( @surgeon_id IS NULL) THEN
SET @surgeon_id = (SELECT last_modified_user_id FROM episode WHERE id=@id);
END IF;

UPDATE temp_episodes_diagnosis SET SurgeonId = @surgeon_id, ConditionId = @condition_id, Eye= (SELECT CASE WHEN eye_id = 1 THEN 'L' WHEN eye_id = 2 THEN 'R' WHEN eye_id = 3 THEN 'Both' WHEN eye_id IS NULL THEN 'N' END ) WHERE id = @id;

END WHILE;

#TODO: Map conditionId and DiagnosisTermId

SET @file = CONCAT(dir, '/episode_diagnosis_', @time_now, '.csv');
SET @cmd = CONCAT("(SELECT 'EpisodeId', 'Eye', 'Date', 'SurgeonId', 'ConditionId', 'DiagnosisTermId')
		   UNION (SELECT id, Eye, last_modified_date, SurgeonId, ConditionId, disorder_id FROM temp_episodes_diagnosis INTO OUTFILE '", @file ,
		   "' FIELDS ENCLOSED BY '\"' TERMINATED BY ';'",
		   "  LINES TERMINATED BY '\r\n')");

PREPARE statement FROM @cmd;
EXECUTE statement;

DROP TABLE temp_episodes_diagnosis;

END;

                        -- EpisodeDiabeticDiagnosis --
                        
DROP PROCEDURE IF EXISTS get_episode_diabetic_diagnosis;
CREATE DEFINER=`root`@`localhost` PROCEDURE get_episode_diabetic_diagnosis(IN dir VARCHAR(255))
BEGIN
SET @time_now = UNIX_TIMESTAMP(NOW());
SET @count = (SELECT COUNT(*) FROM disorder WHERE fully_specified_name LIKE '%diabet%');
SET @diabetes_ids = (SELECT SUBSTRING_INDEX(GROUP_CONCAT(id SEPARATOR ','), ',', @count) FROM disorder WHERE term LIKE '%diabet%');
CREATE TABLE temp_episode_diabetic_diagnosis SELECT e.id, s.patient_id, s.disorder_id, s.date, p.dob FROM secondary_diagnosis s
									 LEFT JOIN disorder d ON d.id = s.disorder_id
									 LEFT JOIN episode e ON e.patient_id = s.patient_id
									 LEFT JOIN patient p ON e.patient_id = p.id;

ALTER TABLE temp_episode_diabetic_diagnosis ADD IsDiabetic TINYINT DEFAULT 0 NOT NULL,
									ADD DiabetesTypeId INTEGER(10),
									ADD DiabetesRegimeId INTEGER(10) DEFAULT 9 NOT NULL,
									ADD AgeAtDiagnosis INTEGER(3),
									ADD edd_id INTEGER NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY(edd_id);

SET @type_1 = ('23045005,28032008,46635009,190368000,190369008,190371008,190372001,199229001,237618001,290002008,313435000,314771006,314893005,
	   314894004,401110002,420270002,420486006,420514000,420789003,420825003,420868002,420918009,421165007,421305000,421365002,421468001,
	   421893009,421920002,422228004,422297002,425159004,425442003,426907004,427571000,428896009,11530004');

SET @type_2 = ('9859006,44054006,81531005,190388001,190389009,190390000,190392008,199230006,237599002,237604008,237614004,237650006,
	   313436004,314772004,314902007,314903002,314904008,359642000,395204000,420279001,420414003,420436000,420715001,420756003,
	   421326000,421631007,421707005,421750000,421779007,421847006,421986006,422014003,422034002,422099009,422166005,423263001,
	   424989000,427027005,427134009,428007007,359638003');

SET @gestational = ('237626009,237627000,11687002,46894009,71546005,75022004,420491007,420738003,420989005,421223006,421389009,421443003,
			422155003,76751001,199223000,199225007,199227004');

SET @midd = ('237619009,359939009');

SET @modd = ('14052004,28453007');

SET @other = ('2751001,4307007,4783006,5368009,5969009,8801005,33559001,42954008,49817004,51002006,57886004,59079001,70694009,
	  73211009,75524006,75682002,111552007,111554008,127012008,190329007,190330002,190331003,190406000,190407009,190410002,
	  190411003,190412005,190416008,190447002,199226008,199228009,199231005,237600004,237601000,237603002,237611007,237612000,
	  237616002,237617006,237620003,238981002,275918005,276560009,408540003,413183008,420422005,420683009,421256007,421895002,
	  422088007,422183001,422275004,426705001,426875007,427089005,441628001,91352004,399144008');

#TODO: Update DiabetesRegimeId

SET @update_cmd = CONCAT('UPDATE temp_episode_diabetic_diagnosis SET IsDiabetic = 1, AgeAtDiagnosis = DATEDIFF(date, dob)/365,
				  DiabetesTypeId = (SELECT CASE WHEN disorder_id IN (',@type_1,') THEN 1
												WHEN disorder_id IN (',@type_2,') THEN 2
												WHEN disorder_id IN (',@gestational,') THEN 3
												WHEN disorder_id IN (',@midd,') THEN 4
												WHEN disorder_id IN (',@modd,') THEN 5
												WHEN disorder_id IN (',@other,') THEN 9
												END )
				  WHERE disorder_id IN (',@diabetes_ids,')');

PREPARE update_statement FROM @update_cmd;
EXECUTE update_statement;



SET @file = CONCAT(dir, '/episode_diabetic_diagnosis_', @time_now, '.csv');
SET @cmd = CONCAT("(SELECT 'EpisodeId', 'IsDiabetic', 'DiabetesTypeId', 'DiabetesRegimeId', 'AgeAtDiagnosis')
  UNION (SELECT id, IsDiabetic, DiabetesTypeId, DiabetesRegimeId, AgeAtDiagnosis FROM temp_episode_diabetic_diagnosis INTO OUTFILE '", @file ,
		  "' FIELDS ENCLOSED BY '\"' TERMINATED BY ';'",
		  "  LINES TERMINATED BY '\r\n')");

PREPARE statement FROM @cmd;
EXECUTE statement;

DROP TABLE temp_episode_diabetic_diagnosis;

END;

                        -- EpisodeDrug --
                        
DROP PROCEDURE IF EXISTS get_episode_drug;
CREATE DEFINER=`root`@`localhost` PROCEDURE get_episode_drug(IN dir VARCHAR(255))
BEGIN
SET @time_now = UNIX_TIMESTAMP(NOW());
CREATE VIEW nod_episode_drug AS SELECT e.id AS EpisodeId , dr.id AS DrugRouteId,
						  (SELECT CASE WHEN option_id = 1 THEN 'L' WHEN option_id = 2 THEN 'R' WHEN option_id = 3 THEN 'B'  ELSE 'N' END) AS Eye,
						  (SELECT CASE WHEN m.drug_id IS NOT NULL THEN (SELECT NAME FROM drug WHERE id = m.drug_id) WHEN m.drug_id IS NULL THEN ''
								  WHEN m.medication_drug_id IS NOT NULL THEN (SELECT NAME FROM medication_drug WHERE id = m.drug_id) WHEN m.medication_drug_id IS NULL THEN '' END) AS DrugId,
						  (SELECT CASE WHEN DAYNAME(m.start_date) IS NULL THEN 1 ELSE 0 END) AS IsStartDateApprox,
						  (SELECT CASE WHEN opi.prescription_id IS NOT NULL THEN 1 ELSE 0 END ) AS IsAddedByPrescription,
						  (SELECT CASE WHEN m.start_date IS NULL THEN '' ELSE m.start_date END) AS StartDate,
						  (SELECT CASE WHEN m.end_date IS NULL THEN '' ELSE m.end_date END) AS StopDate,
						  (SELECT CASE WHEN opi.continue_by_gp IS NULL THEN 0 ELSE opi.continue_by_gp END) AS IsContinueIndefinitely

FROM episode e
INNER JOIN medication m ON e.patient_id = m.patient_id
LEFT JOIN drug_route dr ON dr.id = m.route_id
LEFT JOIN `event` ev ON ev.episode_id = e.id
LEFT JOIN event_type evt ON evt.id = ev.event_type_id
LEFT JOIN et_ophdrprescription_details etp ON etp.event_id = ev.id
LEFT JOIN ophdrprescription_item opi ON etp.id = opi.prescription_id
GROUP BY episode_id;
                        
SET @file = CONCAT(dir, '/episode_drug_', @time_now, '.csv');
SET @cmd = CONCAT("(SELECT 'EpisodeId', 'Eye', 'DrugId', 'DrugRouteId', 'StartDate', 'StopDate', 'IsAddedByPrescription', 'IsContinueIndefinitely', 'IsStartDateApprox')
		  UNION (SELECT EpisodeId, Eye, DrugId, DrugRouteId, StartDate, StopDate, IsAddedByPrescription, IsContinueIndefinitely, IsStartDateApprox FROM nod_episode_drug INTO OUTFILE '", @file,
		  "' FIELDS ENCLOSED BY '\"' TERMINATED BY ';'",
		  "  LINES TERMINATED BY '\r\n')");

PREPARE statement FROM @cmd;
EXECUTE statement;

DROP VIEW nod_episode_drug;

END;

                        -- EpisodeBiometry --
                        
DROP PROCEDURE IF EXISTS get_episode_biometry;
CREATE DEFINER=`root`@`localhost` PROCEDURE get_episode_biometry(IN dir VARCHAR(255))
BEGIN
SET @time_now = UNIX_TIMESTAMP(NOW());
CREATE VIEW nod_episode_biometry AS SELECT e.id AS EpisodeId
							FROM episode e
							LEFT JOIN `event` ev ON ev.episode_id = e.id
							LEFT JOIN event_type et ON et.id = ev.event_type_id
							WHERE et.id = 17;
#TODO update biometry data in database
                        
DROP VIEW nod_episode_biometry;
END;

                        -- EpisodeIOP --
                        
DROP PROCEDURE IF EXISTS get_episode_iop;
CREATE DEFINER=`root`@`localhost` PROCEDURE get_episode_iop(IN dir VARCHAR(255))
BEGIN
SET @time_now = UNIX_TIMESTAMP(NOW());
CREATE VIEW nod_episode_iop AS SELECT e.id AS EpisodeId, oipv.reading_id,
					   (SELECT CASE WHEN oipv.eye_id = 1 THEN 'L' WHEN oipv.eye_id = 2 THEN 'R' END) AS Eye
					   FROM episode e
					   JOIN `event` ev ON ev.episode_id = e.id
					   JOIN event_type et ON et.id = ev.event_type_id
					   JOIN et_ophciexamination_intraocularpressure etoi ON etoi.event_id = ev.id
					   JOIN ophciexamination_intraocularpressure_value oipv ON oipv.element_id = etoi.id
					   WHERE et.name = 'Examination'
					   GROUP BY e.id;

#TODO complete query after talk with Toby
DROP VIEW nod_episode_iop;
END;

                        -- EpisodePreOpAssessment --
                        
DROP PROCEDURE IF EXISTS get_EpisodePreOpAssessment;
CREATE DEFINER=`root`@`localhost` PROCEDURE get_EpisodePreOpAssessment(IN dir VARCHAR(255))
BEGIN
SET @time_now = UNIX_TIMESTAMP(NOW());
CREATE VIEW nod_episode_preop_assessment AS SELECT e.id AS EpisodeId,
									(SELECT CASE WHEN pl.eye_id = 1 THEN 'L' WHEN pl.eye_id = 2 THEN 'R' WHEN pl.eye_id = 3 THEN 'B' END) AS Eye,
									(SELECT CASE WHEN pr.risk_id IS NULL THEN 0 WHEN pr.risk_id = 1 THEN 1 ELSE 0 END) AS IsAbleToLieFlat,
									(SELECT CASE WHEN pr.risk_id IS NULL THEN 0 WHEN pr.risk_id = 4 THEN 1 ELSE 0 END) AS IsInabilityToCooperate
									FROM episode e
									LEFT JOIN `event` ev ON ev.episode_id = e.id
									JOIN et_ophtroperationnote_procedurelist pl ON pl.event_id = ev.id
									LEFT JOIN patient_risk_assignment pr ON pr.patient_id = e.patient_id
									GROUP BY e.id;

SET @file = CONCAT(dir, '/episode_preop_assessment_', @time_now, '.csv');
SET @cmd = CONCAT("(SELECT 'EpisodeId', 'Eye', 'IsAbleToLieFlat', 'IsInabilityToCooperate')
	UNION (SELECT EpisodeId, Eye, IsAbleToLieFlat, IsInabilityToCooperate FROM nod_episode_preop_assessment INTO OUTFILE '", @file,
	"' FIELDS ENCLOSED BY '\"' TERMINATED BY ';'",
	"  LINES TERMINATED BY '\r\n')");

PREPARE statement FROM @cmd;
EXECUTE statement;

DROP VIEW nod_episode_preop_assessment;

END;

                        -- EpisodeRefraction --
                        
DROP PROCEDURE IF EXISTS get_episode_refraction;
CREATE DEFINER=`root`@`localhost` PROCEDURE get_episode_refraction(IN dir VARCHAR(255))
BEGIN
SET @time_now = UNIX_TIMESTAMP(NOW());
CREATE VIEW nod_episode_refraction AS (SELECT e.episode_id AS EpisodeId, r.left_sphere AS Sphere, r.left_cylinder AS Cylinder, r.left_axis AS Axis, '' AS RefractionTypeId, '' AS ReadingAdd,
							  (SELECT CASE WHEN r.eye_id = 1 THEN 'L' END) AS Eye
							  FROM `event` e
							  INNER JOIN et_ophciexamination_refraction r ON r.event_id = e.id
							  WHERE r.eye_id = 1)
							  UNION
							  (SELECT e.episode_id AS EpisodeId, r.right_sphere AS Sphere, r.right_cylinder AS Cylinder, r.right_axis AS Axis, '' AS RefractionTypeId, '' AS ReadingAdd,
							  (SELECT CASE WHEN r.eye_id = 2 THEN 'R' END) AS Eye
							  FROM `event` e
							  INNER JOIN et_ophciexamination_refraction r ON r.event_id = e.id
							  WHERE r.eye_id = 2);


SET @file = CONCAT(dir, '/episode_refraction_', @time_now, '.csv');
SET @cmd = CONCAT("(SELECT 'EpisodeId', 'Eye', 'RefractionTypeId', 'Sphere', 'Cylinder', 'Axis', 'ReadingAdd')
		  UNION (SELECT EpisodeId, Eye, RefractionTypeId, Sphere, Cylinder, Axis, ReadingAdd FROM  nod_episode_refraction INTO OUTFILE '", @file,
		  "' FIELDS ENCLOSED BY '\"' TERMINATED BY ';'",
		  "  LINES TERMINATED BY '\r\n')");

PREPARE statement FROM @cmd;
EXECUTE statement;

DROP VIEW nod_episode_refraction;

END;

                        -- EpisodeVisualAcuity --
                        
DROP PROCEDURE IF EXISTS get_episode_visual_acuity;
CREATE DEFINER=`root`@`localhost` PROCEDURE get_episode_visual_acuity(IN dir VARCHAR(255))
BEGIN
SET @time_now = UNIX_TIMESTAMP(NOW());
CREATE VIEW nod_episode_visual_acuity AS SELECT e.episode_id AS EpisodeId, v.unit_id AS NotationRecordedId,
								   (SELECT CASE WHEN v.eye_id = 1 THEN 'L' WHEN v.eye_id = 2 THEN 'R' END) AS Eye,
								   (SELECT MAX(VALUE) FROM ophciexamination_visualacuity_reading r JOIN et_ophciexamination_visualacuity va ON va.id = r.element_id WHERE r.element_id = v.id AND va.unit_id = (SELECT id FROM ophciexamination_visual_acuity_unit WHERE NAME = 'logMAR single-letter')) AS BestMeasure,
								   #(SELECT value from ophciexamination_visualacuity_reading r JOIN et_ophciexamination_visualacuity va ON va.id = r.element_id WHERE r.element_id = v.id AND method_id = 1) AS Unaided,
								   NULL AS Unaided, NULL AS Pinhole, NULL AS BestCorrected
								 FROM `event` e
								 INNER JOIN et_ophciexamination_visualacuity v ON v.event_id = e.id
								 WHERE v.eye_id != 3;
#TODO: Unaided, Pinhole, BestCorrected

SET @file = CONCAT(dir, '/episode_visual_acuity_', @time_now, '.csv');
SET @cmd = CONCAT("(SELECT 'EpisodeId', 'Eye', 'NotationRecordedId', 'BestMeasure', 'Unaided', 'Pinhole', 'BestCorrected')
   UNION (SELECT EpisodeId, Eye, NotationRecordedId, BestMeasure, Unaided, Pinhole, BestCorrected FROM nod_episode_visual_acuity INTO OUTFILE '", @file,
   "'  FIELDS ENCLOSED BY '\"' TERMINATED BY ';'",
   "  LINES TERMINATED BY '\r\n')");

PREPARE statement FROM @cmd;
EXECUTE statement;

DROP VIEW nod_episode_visual_acuity;

END;
                        
                        -- EpisodeOperation --
                        
DROP PROCEDURE IF EXISTS get_episode_operation;
CREATE DEFINER=`root`@`localhost` PROCEDURE get_episode_operation(IN dir VARCHAR(255))
BEGIN
SET @time_now = UNIX_TIMESTAMP(NOW());
                        
CREATE TEMPORARY TABLE tmp_doctor_grade (
	`code` INT(10) UNSIGNED NOT NULL,
	`desc` VARCHAR(100)
);

INSERT INTO tmp_doctor_grade (`code`, `desc`)
VALUES
(0, 'Consultant'),
(1, 'Locum Consultant'),
(2, 'corneal burn'),
(3, 'Associate Specialist'),
(4, 'Fellow'),
(5, 'Registrar'),
(6, 'Staff Grade'),
(7, 'Trust Doctor'),
(8, 'Senior House Officer'),
(9, 'Specialty trainee (year 1)'),
(10, 'Specialty trainee (year 2)'),
(11, 'Specialty trainee (year 3)'),
(12, 'Specialty trainee (year 4)'),
(13, 'Specialty trainee (year 5)'),
(14, 'Specialty trainee (year 6)'),
(15, 'Specialty trainee (year 7)'),
(16, 'Foundation Year 1 Doctor'),
(17, 'Foundation Year 2 Doctor'),
(18, 'GP with a special interest in ophthalmology'),
(19, 'Community ophthalmologist'),
(20, 'Anaesthetist'),
(21, 'Orthoptist'),
(22, 'Optometrist'),
(23, 'Clinical nurse specialist'),
(24, 'Nurse'),
(25, 'Health Care Assistant'),
(26, 'Ophthalmic Technician'),
(27, 'Surgical Care Practitioner'),
(28, 'Clinical Assistant'),
(29, 'RG1'),
(30, 'RG2'),
(31, 'ODP'),
(32, 'Administration staff'),
(33, 'Other');
                        
CREATE TABLE nod_episode_operation AS SELECT e.id AS OperationId, e.episode_id AS EpisodeId, e.event_date AS ListedDate, 
    s.surgeon_id AS SurgeonId, 
    (
        SELECT `code`
        FROM tmp_doctor_grade, doctor_grade
        WHERE user.`doctor_grade_id` = doctor_grade.id AND doctor_grade.`grade` = tmp_doctor_grade.desc
    ) AS SurgeonGradeId
            FROM `event` e
            JOIN event_type evt ON evt.id = e.event_type_id
            LEFT JOIN et_ophtroperationnote_surgeon s ON s.event_id = e.id
            INNER JOIN `user` ON s.`surgeon_id` = `user`.`id`
            WHERE evt.name = 'Operation booking';

SET @file = CONCAT(dir, '/episode_operation_', @time_now, '.csv');
#SET @cmd = ();
                        
SET @cmd = CONCAT("
                (SELECT 'OperationId', 'EpisodeId', 'ListedDate', 'SurgeonId', 'SurgeonGradeId')
                UNION
                (SELECT * FROM nod_episode_operation INTO OUTFILE '", @file,
		  "' FIELDS ENCLOSED BY '\"' TERMINATED BY ';'",
		  "  LINES TERMINATED BY '\r\n')");
                        
PREPARE statement FROM @cmd;
EXECUTE statement;

DROP TABLE nod_episode_operation;
DROP TEMPORARY TABLE tmp_doctor_grade;
                        
END;

                        -- EpisodeOperationComplication --

DROP PROCEDURE IF EXISTS get_episode_operation_complication;
CREATE DEFINER=`root`@`localhost` PROCEDURE get_episode_operation_complication(IN dir VARCHAR(255))
BEGIN
SET @time_now = UNIX_TIMESTAMP(NOW());
SET @file = CONCAT(dir, '/episode_operation_complication_', @time_now, '.csv');

CREATE TEMPORARY TABLE tmp_complication_type (
	`code` INT(10) UNSIGNED NOT NULL,
	`name` VARCHAR(100)
);

INSERT INTO tmp_complication_type (`code`, `name`)
VALUES
    (0, 'None'),
    (1, 'choroidal / suprachoroidal haemorrhage'),
    (2, 'corneal burn'),
    (3, 'corneal epithelial abrasion'),
    (4, 'corneal oedema'),
    (5, 'endothelial damage / Descemet\'s tear'),
    (6, 'epithelial abrasion'),
    (7, 'hyphaema'),
    (8, 'IOL into the vitreous'),
    (9, 'iris prolapse'),
    (10, 'iris trauma'),
    (11, 'lens exchange required / other IOL problems'),
    (12, 'nuclear / epinuclear fragment into vitreous'),
    (13, 'PC rupture - no vitreous loss'),
    (14, 'PC rupture - vitreous loss'),
    (15, 'phaco burn / wound problems'),
    (16, 'suprachoroidal haemorrhage'),
    (17, 'torn iris / damage from the phaco'),
    (18, 'vitreous loss'),
    (19, 'vitreous to the section at end of surgery'),
    (20, 'zonule dialysis'),
    (21, 'zonule rupture - vitreous loss'),
    (25, 'Not recorded'),
    (999, 'other');
                        
SET @cmd = CONCAT(" (SELECT 'OperationId', 'Eye', 'ComplicationTypeId' )
                    UNION
                    (SELECT
                        event.id AS OperationId, 
                        (SELECT CASE 
                            WHEN et_ophtroperationnote_procedurelist.eye_id = 1 THEN 'L' 
                            WHEN et_ophtroperationnote_procedurelist.eye_id = 2 THEN 'R' 
                            WHEN et_ophtroperationnote_procedurelist.eye_id = 3 THEN 'B' 
                            END
                        ) AS Eye,
                        (SELECT `code` 
                            FROM tmp_complication_type 
                            WHERE tmp_complication_type.`name` = ophtroperationnote_cataract_complications.name
                        ) AS ComplicationTypeId
                    FROM ophtroperationnote_cataract_complication
                    INNER JOIN `et_ophtroperationnote_cataract` ON `ophtroperationnote_cataract_complication`.cataract_id = et_ophtroperationnote_cataract.id
                    INNER JOIN ophtroperationnote_cataract_complications ON ophtroperationnote_cataract_complication.`complication_id` = ophtroperationnote_cataract_complications.`id`
                    INNER JOIN `event` ON  et_ophtroperationnote_cataract.`event_id` = `event`.id
                    INNER JOIN et_ophtroperationnote_procedurelist ON event.id = et_ophtroperationnote_procedurelist.event_id 
                INTO OUTFILE '", @file,
                "' FIELDS ENCLOSED BY '\"' TERMINATED BY ';'",
                "  LINES TERMINATED BY '\r\n')");

PREPARE statement FROM @cmd;
EXECUTE statement;

DROP TEMPORARY TABLE IF EXISTS tmp_complication_type;

END;

                        -- Run Export Generation --
                        
DROP PROCEDURE IF EXISTS run_nod_export_generator;
CREATE DEFINER=`root`@`localhost` PROCEDURE run_nod_export_generator(IN dir VARCHAR(255))
BEGIN

CALL get_surgeons(dir);
CALL get_patients(dir);
CALL get_patient_cvi_status(dir);
CALL get_nod_episodes(dir);
CALL get_episodes_diagnosis(dir);
CALL get_episode_diabetic_diagnosis(dir);
CALL get_episode_drug(dir);
CALL get_episode_biometry(dir);
CALL get_episode_iop(dir);
CALL get_EpisodePreOpAssessment(dir);
CALL get_episode_refraction(dir);
CALL get_episode_visual_acuity(dir);
CALL get_episode_operation(dir);
CALL get_episode_operation_complication(dir);
   
#EpisodeOperationIndication
                        
#EpisodeOperationCoPathology
                        
#EpisodeOperationAnaesthesia
                        
#EpisodeTreatment
                        
#EpisodeTreatmentRetinopexy
#Not returning in this phase
                        
#EpisodeTreatmentCataract
#Where ophtroperationnote_procedurelist_procedure_assignment contains a proc_id that matches the cataract element_type_id in ophtroperationnote_procedure_element
                        
#EpisodeTreatmentVR
#Not returning in this phase
                        
#EpisodeTreatmentTrabeculectomy
#Not returning in this phase
                        
#EpisodeTreatmentInjection
#Not returning in this phase
    
#EpisodeTreatmentLaser
#Not returning in this phase
                        
#EpisodePostOpComplication
#This functionality does not exist at time of writing. It needs adding and is in Jira as ticket OE-5690

END;

EOL;
                
                
            $this->execute($storedProcedure);
            return true;
	}

	public function down()
	{
                $storedProcedure = <<<EOL

DROP PROCEDURE IF EXISTS get_surgeons;
DROP PROCEDURE IF EXISTS get_patients;
DROP PROCEDURE IF EXISTS get_patient_cvi_status;
DROP PROCEDURE IF EXISTS get_nod_episodes;
DROP PROCEDURE IF EXISTS get_episodes_diagnosis;
DROP PROCEDURE IF EXISTS get_episode_diabetic_diagnosis;
DROP PROCEDURE IF EXISTS get_episode_drug;
DROP PROCEDURE IF EXISTS get_episode_biometry;
DROP PROCEDURE IF EXISTS get_episode_iop;
DROP PROCEDURE IF EXISTS get_EpisodePreOpAssessment;
DROP PROCEDURE IF EXISTS get_episode_refraction;
DROP PROCEDURE IF EXISTS get_episode_visual_acuity;
DROP PROCEDURE IF EXISTS get_episode_operation;
DROP PROCEDURE IF EXISTS get_episode_operation_complication;
DROP PROCEDURE IF EXISTS run_nod_export_generator;

EOL;
		$this->execute($storedProcedure);
                return true;
	}
}
