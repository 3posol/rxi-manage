<?php

function pdf_application ($data, $output = 'I') {
	$pdf_file_sufix = '-50';

	$pdf = new FPDI();
	$pageCount = $pdf->setSourceFile("Enrollment-Form-20181210" . $pdf_file_sufix . ".pdf");

	$showCellBorder = 0;
	$pdf->SetFont('Helvetica', '', 12);

	//
	//PAGE 1
	//

	$tplIdx = $pdf->importPage(1, '/MediaBox');
	$pdf->addPage();
	$pdf->useTemplate($tplIdx, 0, 0, 210);

	//Keep for you records.
	$pdf->Image('images/keep.png', 10, 10, 0, 0);

	//Name
	$pdf->SetXY(28, 27);
	$pdf->MultiCell(49, 6, ((isset($data['p_first_name'])) ? $data['p_first_name'] : ''), $showCellBorder, 'C');

	$pdf->SetXY(100, 27);
	$pdf->MultiCell(16, 6, ((isset($data['p_middle_initial'])) ? $data['p_middle_initial'] : ''), $showCellBorder, 'C');

	$pdf->SetXY(136, 27);
	$pdf->MultiCell(66, 6, ((isset($data['p_last_name'])) ? $data['p_last_name'] : ''), $showCellBorder, 'C');

	//DOB
	$pdf->SetXY(32, 33);
	$pdf->MultiCell(8, 6, ((isset($data['p_dob'])) ? substr($data['p_dob'], 0, 2) : ''), $showCellBorder, 'L');
	//
	$pdf->SetXY(45, 33);
	$pdf->MultiCell(8, 6, ((isset($data['p_dob'])) ? substr($data['p_dob'], 3, 2) : ''), $showCellBorder, 'L');
	//
	$pdf->SetXY(58, 33);
	$pdf->MultiCell(19, 6, ((isset($data['p_dob'])) ? substr($data['p_dob'], 6, 4) : ''), $showCellBorder, 'L');

	//Gender - F
	$pdf->SetXY(95, 33);
	$pdf->MultiCell(5, 6, ((isset($data['p_gender']) && $data['p_gender'] == 'F') ? 'x' : ''), $showCellBorder, 'C');
	// - M
	$pdf->SetXY(108, 33);
	$pdf->MultiCell(5, 6, ((isset($data['p_gender']) && $data['p_gender'] == 'M') ? 'x' : ''), $showCellBorder, 'C');

	//SSN
	$pdf->SetXY(142, 33);
	//$pdf->MultiCell(10, 6, ((isset($data['p_ssn'])) ? substr(preg_replace("/[^0-9]/", '', $data['p_ssn']), 0, 3) : ''), $showCellBorder, 'L');
	$pdf->MultiCell(10, 6, ((isset($data['p_ssn'])) ? '***' : ''), $showCellBorder, 'L');
	//
	$pdf->SetXY(156, 33);
	//$pdf->MultiCell(8, 6, ((isset($data['p_ssn'])) ? substr(preg_replace("/[^0-9]/", '', $data['p_ssn']), 3, 2) : ''), $showCellBorder, 'L');
	$pdf->MultiCell(8, 6, ((isset($data['p_ssn'])) ? '**' : ''), $showCellBorder, 'L');
	//
	$pdf->SetXY(168, 33);
	$pdf->MultiCell(19, 6, ((isset($data['p_ssn'])) ? substr(preg_replace("/[^0-9]/", '', $data['p_ssn']), 5, 4) : ''), $showCellBorder, 'L');

	//Address
	$pdf->SetXY(28, 40);
	$pdf->MultiCell(87, 6, ((isset($data['p_address'])) ? $data['p_address'] : ''), $showCellBorder, 'C');

	//City
	$pdf->SetXY(136, 40);
	$pdf->MultiCell(66, 6, ((isset($data['p_city'])) ? $data['p_city'] : ''), $showCellBorder, 'C');

	//State
	$pdf->SetXY(28, 46);
	$pdf->MultiCell(13, 6, ((isset($data['p_state'])) ? $data['p_state'] : ''), $showCellBorder, 'C');

	//ZipCode
	$pdf->SetXY(56, 46);
	$pdf->MultiCell(21, 6, ((isset($data['p_zip'])) ? $data['p_zip'] : ''), $showCellBorder, 'C');

	//Phone
	$pdf->SetXY(92, 46);
	$pdf->MultiCell(11, 6, ((isset($data['p_phone'])) ? substr(preg_replace("/[^0-9]/", '', $data['p_phone']), 0, 3) : ''), $showCellBorder, 'C');
	//
	$pdf->SetXY(104, 46);
	$pdf->MultiCell(12, 6, ((isset($data['p_phone'])) ? substr(preg_replace("/[^0-9]/", '', $data['p_phone']), 3, 3) : ''), $showCellBorder, 'C');
	//
	$pdf->SetXY(120, 46);
	$pdf->MultiCell(20, 6, ((isset($data['p_phone'])) ? substr(preg_replace("/[^0-9]/", '', $data['p_phone']), 6, 4) : ''), $showCellBorder, 'L');

	//Fax
	$pdf->SetXY(153, 46);
	$pdf->MultiCell(11, 6, ((isset($data['p_fax'])) ? substr(preg_replace("/[^0-9]/", '', $data['p_fax']), 0, 3) : ''), $showCellBorder, 'C');
	//
	$pdf->SetXY(165, 46);
	$pdf->MultiCell(12, 6, ((isset($data['p_fax'])) ? substr(preg_replace("/[^0-9]/", '', $data['p_fax']), 3, 3) : ''), $showCellBorder, 'C');
	//
	$pdf->SetXY(181, 46);
	$pdf->MultiCell(20, 6, ((isset($data['p_fax'])) ? substr(preg_replace("/[^0-9]/", '', $data['p_fax']), 6, 4) : ''), $showCellBorder, 'L');

	//E-mail
	$pdf->SetXY(28, 52);
	$pdf->MultiCell(114, 6, ((isset($data['p_email'])) ? $data['p_email'] : ''), $showCellBorder, 'C');

	//No of people in household
	$pdf->SetXY(186, 52);
	$pdf->MultiCell(16, 6, ((isset($data['p_household'])) ? $data['p_household'] : ''), $showCellBorder, 'C');

	//Alternate Contact
	$pdf->SetXY(56, 59);
	$pdf->MultiCell(60, 6, ((isset($data['p_alternate_contact_name'])) ? $data['p_alternate_contact_name'] : ''), $showCellBorder, 'C');

	//Alternate Contact Phone
	$pdf->SetXY(153, 59);
	$pdf->MultiCell(11, 6, ((isset($data['p_alternate_phone'])) ? substr(preg_replace("/[^0-9]/", '', $data['p_alternate_phone']), 0, 3) : ''), $showCellBorder, 'C');
	//
	$pdf->SetXY(165, 59);
	$pdf->MultiCell(12, 6, ((isset($data['p_alternate_phone'])) ? substr(preg_replace("/[^0-9]/", '', $data['p_alternate_phone']), 3, 3) : ''), $showCellBorder, 'C');
	//
	$pdf->SetXY(181, 59);
	$pdf->MultiCell(20, 6, ((isset($data['p_alternate_phone'])) ? substr(preg_replace("/[^0-9]/", '', $data['p_alternate_phone']), 6, 4) : ''), $showCellBorder, 'L');

	//Marital Status - Single
	$pdf->SetXY(34, 66);
	$pdf->MultiCell(5, 6, ((isset($data['p_married']) && $data['p_married'] == 'S') ? 'x' : ''), $showCellBorder, 'C');
	// - Married
	$pdf->SetXY(52, 66);
	$pdf->MultiCell(5, 6, ((isset($data['p_married']) && $data['p_married'] == 'M') ? 'x' : ''), $showCellBorder, 'C');
	// - Divorced
	$pdf->SetXY(71, 66);
	$pdf->MultiCell(5, 6, ((isset($data['p_married']) && $data['p_married'] == 'D') ? 'x' : ''), $showCellBorder, 'C');
	// - Widowed
	$pdf->SetXY(91, 66);
	$pdf->MultiCell(5, 6, ((isset($data['p_married']) && $data['p_married'] == 'W') ? 'x' : ''), $showCellBorder, 'C');

	//Employment Status - Full time
	$pdf->SetXY(134, 66);
	$pdf->MultiCell(5, 6, ((isset($data['p_employment_status']) && $data['p_employment_status'] == 'F') ? 'x' : ''), $showCellBorder, 'C');
	// - Part time
	$pdf->SetXY(154, 66);
	$pdf->MultiCell(5, 6, ((isset($data['p_employment_status']) && $data['p_employment_status'] == 'P') ? 'x' : ''), $showCellBorder, 'C');
	// - Retired
	$pdf->SetXY(171, 66);
	$pdf->MultiCell(5, 6, ((isset($data['p_employment_status']) && $data['p_employment_status'] == 'R') ? 'x' : ''), $showCellBorder, 'C');
	// - Unemployed
	$pdf->SetXY(195, 66);
	$pdf->MultiCell(5, 6, ((isset($data['p_employment_status']) && $data['p_employment_status'] == 'U') ? 'x' : ''), $showCellBorder, 'C');

	//US Citizen - Yes
	$pdf->SetXY(83, 72);
	$pdf->MultiCell(5, 6, ((isset($data['p_uscitizen']) && (bool) $data['p_uscitizen']) ? 'x' : ''), $showCellBorder, 'C');
	// - No
	$pdf->SetXY(96, 72);
	$pdf->MultiCell(5, 6, ((isset($data['p_uscitizen']) && ! (bool) $data['p_uscitizen']) ? 'x' : ''), $showCellBorder, 'C');

	//Disabled - Yes
	$pdf->SetXY(181, 72);
	$pdf->MultiCell(5, 6, ((isset($data['p_disabled_status']) && (bool) $data['p_disabled_status']) ? 'x' : ''), $showCellBorder, 'C');
	// - No
	$pdf->SetXY(193, 72);
	$pdf->MultiCell(5, 6, ((isset($data['p_disabled_status']) && ! (bool) $data['p_disabled_status']) ? 'x' : ''), $showCellBorder, 'C');

	//Medicare - Yes
	$pdf->SetXY(83, 79);
	$pdf->MultiCell(5, 6, ((isset($data['p_medicare']) && (bool) $data['p_medicare']) ? 'x' : ''), $showCellBorder, 'C');
	// - No
	$pdf->SetXY(96, 79);
	$pdf->MultiCell(5, 6, ((isset($data['p_medicare']) && ! (bool) $data['p_medicare']) ? 'x' : ''), $showCellBorder, 'C');

	//Medicare Part D - Yes
	$pdf->SetXY(181, 79);
	$pdf->MultiCell(5, 6, ((isset($data['p_medicare_part_d']) && (bool) $data['p_medicare_part_d']) ? 'x' : ''), $showCellBorder, 'C');
	// - No
	$pdf->SetXY(193, 79);
	$pdf->MultiCell(5, 6, ((isset($data['p_medicare_part_d']) && ! (bool) $data['p_medicare_part_d']) ? 'x' : ''), $showCellBorder, 'C');

	//Medicaid - Yes
	$pdf->SetXY(83, 85);
	$pdf->MultiCell(5, 6, ((isset($data['p_medicaid']) && (bool) $data['p_medicaid']) ? 'x' : ''), $showCellBorder, 'C');
	// - No
	$pdf->SetXY(96, 85);
	$pdf->MultiCell(5, 6, ((isset($data['p_medicaid']) && ! (bool) $data['p_medicaid']) ? 'x' : ''), $showCellBorder, 'C');

	//LIS - Yes
	$pdf->SetXY(181, 85);
	$pdf->MultiCell(5, 6, ((isset($data['p_lis']) && (bool) $data['p_lis']) ? 'x' : ''), $showCellBorder, 'C');
	// - No
	$pdf->SetXY(193, 85);
	$pdf->MultiCell(5, 6, ((isset($data['p_lis']) && ! (bool) $data['p_lis']) ? 'x' : ''), $showCellBorder, 'C');

	//Doctor 1
	if (isset($data['doctors']) && count($data['doctors']) > 0) {
		//- Name
		$pdf->SetXY(30, 109);
		$pdf->MultiCell(75, 6, ((isset($data['doctors'][1]['doctor_first_name']) && isset($data['doctors'][1]['doctor_last_name'])) ? $data['doctors'][1]['doctor_first_name'] . ' ' . $data['doctors'][1]['doctor_last_name'] : ''), $showCellBorder, 'C');
		//- Facility Name
		$pdf->SetXY(30, 115);
		$pdf->MultiCell(75, 6, ((isset($data['doctors'][1]['doctor_facility'])) ? $data['doctors'][1]['doctor_facility'] : ''), $showCellBorder, 'C');
		//- Address / Suite
		$pdf->SetXY(30, 121);
		$pdf->MultiCell(75, 6, ((isset($data['doctors'][1]['doctor_address'])) ? $data['doctors'][1]['doctor_address'] : '') . ((isset($data['doctors'][1]['doctor_address2']) && $data['doctors'][1]['doctor_address2'] != '') ? ', ' . $data['doctors'][1]['doctor_address2'] : ''), $showCellBorder, 'C');
		//- City / State / Zip
		$pdf->SetXY(30, 128);
		$pdf->MultiCell(75, 6, ((isset($data['doctors'][1]['doctor_city'])) ? $data['doctors'][1]['doctor_city'] : '') . ((isset($data['doctors'][1]['doctor_state'])) ? ', ' . $data['doctors'][1]['doctor_state'] : '') . ((isset($data['doctors'][1]['doctor_zip'])) ? ', ' . $data['doctors'][1]['doctor_zip'] : ''), $showCellBorder, 'C');
		//Phone
		$pdf->SetXY(22, 134);
		$pdf->MultiCell(10, 6, ((isset($data['doctors'][1]['doctor_phone'])) ? substr(preg_replace("/[^0-9]/", '', $data['doctors'][1]['doctor_phone']), 0, 3) : ''), $showCellBorder, 'C');
		//
		$pdf->SetXY(32, 134);
		$pdf->MultiCell(10, 6, ((isset($data['doctors'][1]['doctor_phone'])) ? substr(preg_replace("/[^0-9]/", '', $data['doctors'][1]['doctor_phone']), 3, 3) : ''), $showCellBorder, 'C');
		//
		$pdf->SetXY(43, 134);
		$pdf->MultiCell(14, 6, ((isset($data['doctors'][1]['doctor_phone'])) ? substr(preg_replace("/[^0-9]/", '', $data['doctors'][1]['doctor_phone']), 6, 4) : ''), $showCellBorder, 'L');
		//Fax
		$pdf->SetXY(68, 134);
		$pdf->MultiCell(10, 6, ((isset($data['doctors'][1]['doctor_fax'])) ? substr(preg_replace("/[^0-9]/", '', $data['doctors'][1]['doctor_fax']), 0, 3) : ''), $showCellBorder, 'C');
		//
		$pdf->SetXY(78, 134);
		$pdf->MultiCell(10, 6, ((isset($data['doctors'][1]['doctor_fax'])) ? substr(preg_replace("/[^0-9]/", '', $data['doctors'][1]['doctor_fax']), 3, 3) : ''), $showCellBorder, 'C');
		//
		$pdf->SetXY(89, 134);
		$pdf->MultiCell(14, 6, ((isset($data['doctors'][1]['doctor_fax'])) ? substr(preg_replace("/[^0-9]/", '', $data['doctors'][1]['doctor_fax']), 6, 4) : ''), $showCellBorder, 'L');
	}

	// - How did you heard about us
	$pdf->SetXY(90, 98);
	$pdf->MultiCell(111, 6, ((isset($data['p_hear_about'])) ? $data['p_hear_about'] : ''), $showCellBorder, 'C');

	//Doctor 2
	if (isset($data['doctors']) && count($data['doctors']) > 1) {
		//- Name
		$pdf->SetXY(127, 109);
		$pdf->MultiCell(75, 6, ((isset($data['doctors'][2]['doctor_first_name']) && isset($data['doctors'][2]['doctor_last_name'])) ? $data['doctors'][2]['doctor_first_name'] . ' ' . $data['doctors'][2]['doctor_last_name'] : ''), $showCellBorder, 'C');
		//- Facility Name
		$pdf->SetXY(127, 115);
		$pdf->MultiCell(75, 6, ((isset($data['doctors'][2]['doctor_facility'])) ? $data['doctors'][2]['doctor_facility'] : ''), $showCellBorder, 'C');
		//- Address / Suite
		$pdf->SetXY(127, 121);
		$pdf->MultiCell(75, 6, ((isset($data['doctors'][2]['doctor_address'])) ? $data['doctors'][2]['doctor_address'] : '') . ((isset($data['doctors'][2]['doctor_address2'])) ? ', ' . $data['doctors'][2]['doctor_address2'] : ''), $showCellBorder, 'C');
		//- City / State / Zip
		$pdf->SetXY(127, 128);
		$pdf->MultiCell(75, 6, ((isset($data['doctors'][2]['doctor_city'])) ? $data['doctors'][2]['doctor_city'] : '') . ((isset($data['doctors'][2]['doctor_state'])) ? ', ' . $data['doctors'][2]['doctor_state'] : '') . ((isset($data['doctors'][2]['doctor_zip'])) ? ', ' . $data['doctors'][2]['doctor_zip'] : ''), $showCellBorder, 'C');
		//Phone
		$pdf->SetXY(120, 134);
		$pdf->MultiCell(10, 6, ((isset($data['doctors'][2]['doctor_phone'])) ? substr(preg_replace("/[^0-9]/", '', $data['doctors'][2]['doctor_phone']), 0, 3) : ''), $showCellBorder, 'C');
		//
		$pdf->SetXY(130, 134);
		$pdf->MultiCell(10, 6, ((isset($data['doctors'][2]['doctor_phone'])) ? substr(preg_replace("/[^0-9]/", '', $data['doctors'][2]['doctor_phone']), 3, 3) : ''), $showCellBorder, 'C');
		//
		$pdf->SetXY(141, 134);
		$pdf->MultiCell(14, 6, ((isset($data['doctors'][2]['doctor_phone'])) ? substr(preg_replace("/[^0-9]/", '', $data['doctors'][2]['doctor_phone']), 6, 4) : ''), $showCellBorder, 'L');
		//Fax
		$pdf->SetXY(165, 134);
		$pdf->MultiCell(10, 6, ((isset($data['doctors'][2]['doctor_fax'])) ? substr(preg_replace("/[^0-9]/", '', $data['doctors'][2]['doctor_fax']), 0, 3) : ''), $showCellBorder, 'C');
		//
		$pdf->SetXY(175, 134);
		$pdf->MultiCell(10, 6, ((isset($data['doctors'][2]['doctor_fax'])) ? substr(preg_replace("/[^0-9]/", '', $data['doctors'][2]['doctor_fax']), 3, 3) : ''), $showCellBorder, 'C');
		//
		$pdf->SetXY(186, 134);
		$pdf->MultiCell(14, 6, ((isset($data['doctors'][2]['doctor_fax'])) ? substr(preg_replace("/[^0-9]/", '', $data['doctors'][2]['doctor_fax']), 6, 4) : ''), $showCellBorder, 'L');
	}

	//Medication
	if (isset($data['medication'])) {
		//for ($i = 0; $i < 8; $i++) {
		foreach ($data['medication'] as $key => $medication) {
			if ($key <= 8) {
				// #1 - Doctor
				$pdf->SetXY(9, 153 + (($key - 1) * 5));
				$pdf->MultiCell(26, 5, $medication['medication_doctor'], $showCellBorder, 'C');
				// #1 - Medication
				$pdf->SetXY(36, 153 + (($key - 1) * 5));
				$pdf->MultiCell(69, 5, $medication['medication_name'], $showCellBorder, 'C');
				// #1 - Strength
				$pdf->SetXY(106, 153 + (($key - 1) * 5));
				$pdf->MultiCell(21, 5, $medication['medication_strength'], $showCellBorder, 'C');
				// #1 - Frequency
				$pdf->SetXY(128, 153 + (($key - 1) * 5));
				$pdf->MultiCell(74, 5, $medication['medication_frequency'], $showCellBorder, 'C');
			}
		}
	}

	//Salary
	$pdf->SetXY(44, 198);
	$pdf->MultiCell(30, 6, ((isset($data['p_income_salary'])) ? sprintf('%0.2f', preg_replace("/[^0-9.]/", "", $data['p_income_salary'])) : ''), $showCellBorder, 'C');

	//Pension
	$pdf->SetXY(111, 198);
	$pdf->MultiCell(30, 6, ((isset($data['p_income_pension'])) ? sprintf('%0.2f', preg_replace("/[^0-9.]/", "", $data['p_income_pension'])) : ''), $showCellBorder, 'C');

	//SS Retirement
	$pdf->SetXY(177, 198);
	$pdf->MultiCell(25, 6, ((isset($data['p_income_ss_retirement'])) ? sprintf('%0.2f', preg_replace("/[^0-9.]/", "", $data['p_income_ss_retirement'])) : ''), $showCellBorder, 'C');

	//Unemployment
	$pdf->SetXY(44, 205);
	$pdf->MultiCell(30, 6, ((isset($data['p_income_unemployment'])) ? sprintf('%0.2f', preg_replace("/[^0-9.]/", "", $data['p_income_unemployment'])) : ''), $showCellBorder, 'C');

	//Annuity
	$pdf->SetXY(111, 205);
	$pdf->MultiCell(30, 6, ((isset($data['p_income_annuity'])) ? sprintf('%0.2f', preg_replace("/[^0-9.]/", "", $data['p_income_annuity'])) : ''), $showCellBorder, 'C');

	//SS Disability
	$pdf->SetXY(177, 205);
	$pdf->MultiCell(25, 6, ((isset($data['p_income_ss_disability'])) ? sprintf('%0.2f', preg_replace("/[^0-9.]/", "", $data['p_income_ss_disability'])) : ''), $showCellBorder, 'C');

	$pdf->AddFont('DancingScript-Bold', '', 'DancingScript-Bold.php');
	$pdf->SetFont('DancingScript-Bold', '', 12);

	//No Income
	$pdf->SetXY(44, 222);
	$pdf->MultiCell(60, 6, ((isset($data['p_income_zero']) && (bool) $data['p_income_zero']) ? $data['p_first_name'] . ' ' . $data['p_last_name'] : ''), $showCellBorder, 'C');

	//No Tax Filing
	$pdf->SetXY(140, 222);
	$pdf->MultiCell(60, 6, ((isset($data['p_income_file_tax_return']) && (bool) $data['p_income_file_tax_return']) ? $data['p_first_name'] . ' ' . $data['p_last_name'] : ''), $showCellBorder, 'C');

	$pdf->SetFont('Helvetica', '', 12);

	//Checking Account
	$pdf->SetXY(34, 242);
	$pdf->MultiCell(5, 5, ((isset($data['p_payment_method']) && $data['p_payment_method'] == 'ach') ? 'X' : ''), $showCellBorder, 'C');

	//Credit Card
	if (isset($data['p_payment_method']) && $data['p_payment_method'] == 'cc') {
		// - VISA
		$pdf->SetXY(23, 240);
		$pdf->MultiCell(5, 5, ((isset($data['p_cc_type']) && $data['p_cc_type'] == 'Visa') ? 'X' : ''), $showCellBorder, 'C');
		// - Mastercard
		$pdf->SetXY(47, 240);
		$pdf->MultiCell(5, 5, ((isset($data['p_cc_type']) && $data['p_cc_type'] == 'Mastercard') ? 'X' : ''), $showCellBorder, 'C');
		// - American Express
		$pdf->SetXY(23, 249);
		$pdf->MultiCell(5, 5, ((isset($data['p_cc_type']) && $data['p_cc_type'] == 'American Express') ? 'X' : ''), $showCellBorder, 'C');
		// - Discover
		$pdf->SetXY(47, 249);
		$pdf->MultiCell(5, 5, ((isset($data['p_cc_type']) && $data['p_cc_type'] == 'Discover') ? 'X' : ''), $showCellBorder, 'C');

		// - CC No #1
		$cc_no = sprintf('%016s', ((isset($data['p_cc_number'])) ? $data['p_cc_number'] : ''));
		for ($i = 0; $i < 16; $i++){
			$pdf->SetXY(57 + ($i * 5.6), 242);
			$pdf->MultiCell(5, 6, ($i >= 12) ? $cc_no[$i] : '*', $showCellBorder, 'C');
		}
		// - CC Exp Month
		//$cc_exp_mo = sprintf('%02s', ((isset($data['p_cc_exp_month'])) ? $data['p_cc_exp_month'] : ''));
		//$pdf->SetXY(120, 243);
		//$pdf->MultiCell(6, 6, $cc_exp_mo[0], $showCellBorder, 'C');
		//$pdf->SetXY(126, 243);
		//$pdf->MultiCell(6, 6, $cc_exp_mo[1], $showCellBorder, 'C');

		// - CC Exp Year
		//$cc_exp_year = ((isset($data['p_cc_exp_year'])) ? substr($data['p_cc_exp_year'], strlen($data['p_cc_exp_year']) - 2) : '');
		//$pdf->SetXY(137, 243);
		//$pdf->MultiCell(6, 6, $cc_exp_year[0], $showCellBorder, 'C');
		//$pdf->SetXY(143, 243);
		//$pdf->MultiCell(6, 6, $cc_exp_year[1], $showCellBorder, 'C');

		// - CC CVV
		//$pdf->SetXY(159, 243);
		//$pdf->MultiCell(6, 6, $data['p_cc_cvv'][0], $showCellBorder, 'C');

		//$pdf->SetXY(165, 243);
		//$pdf->MultiCell(6, 6, $data['p_cc_cvv'][1], $showCellBorder, 'C');

		//$pdf->SetXY(171, 243);
		//$pdf->MultiCell(6, 6, $data['p_cc_cvv'][2], $showCellBorder, 'C');
	}

	//
	//PAGE 2
	//

	$tplIdx = $pdf->importPage(2, '/MediaBox');
	$pdf->addPage();
	$pdf->useTemplate($tplIdx, 0, 0, 210);

	//Keep for you records.
	$pdf->Image('images/keep.png', 10, 10, 0, 0);

	//Date
	$pdf->SetXY(21, 241);
	$pdf->MultiCell(28, 5, date('m/d/Y'), $showCellBorder, 'C');

	$pdf->AddFont('DancingScript-Bold', '', 'DancingScript-Bold.php');
	$pdf->SetFont('DancingScript-Bold', '', 12);

	$patient_initials = $data['p_first_name'][0] . ' ' . $data['p_last_name'][0];

	//#1 Signature
	$pdf->SetXY(192, 96.5);
	$pdf->MultiCell(10, 5, ((isset($data['p_payment_agreement']) && (bool) $data['p_payment_agreement']) ? $patient_initials : ''), $showCellBorder, 'C');

	//#2 Signature
	$pdf->SetXY(192, 138.7);
	$pdf->MultiCell(10, 5, ((isset($data['p_service_agreement']) && (bool) $data['p_service_agreement']) ? $patient_initials : ''), $showCellBorder, 'C');

	//#3 Signature
	$pdf->SetXY(192, 204);
	$pdf->MultiCell(10, 5, ((isset($data['p_guaranty_agreement']) && (bool) $data['p_guaranty_agreement']) ? $patient_initials : ''), $showCellBorder, 'C');

	//#4 Signature
	$pdf->SetXY(37, 232);
	$pdf->MultiCell(60, 6, $data['p_first_name'] . ' ' . $data['p_last_name'], $showCellBorder, 'C');

	$rs = $pdf->Output('Prescription_Hope_Application.pdf', $output);

	if ($output == 'S') {
		return $rs;
	}
}

function pdf_application_new ($data, $output = 'I') {
	$pdf_file_sufix = '';
	if (isset($_SESSION['rate'])) {
		if (in_array((int) $_SESSION['rate'], array(30, 35))) {
			$pdf_file_sufix = '-' . ((int) $_SESSION['rate']);
		}
	}

	$pdf = new FPDI();
	$pageCount = $pdf->setSourceFile("PH-Enrollment-Form" . $pdf_file_sufix . ".pdf");

	$showCellBorder = 0;
	$pdf->SetFont('Helvetica', '', 12);

	//
	//PAGE 1
	//

	$tplIdx = $pdf->importPage(1, '/MediaBox');
	$pdf->addPage();
	$pdf->useTemplate($tplIdx, 0, 0, 210);

	//Keep for you records.
	$pdf->Image('images/keep.png', 10, 10, 0, 0);

	//Name
	$pdf->SetXY(28, 27);
	$pdf->MultiCell(49, 6, ((isset($data['p_first_name'])) ? $data['p_first_name'] : ''), $showCellBorder, 'C');

	$pdf->SetXY(100, 27);
	$pdf->MultiCell(16, 6, ((isset($data['p_middle_initial'])) ? $data['p_middle_initial'] : ''), $showCellBorder, 'C');

	$pdf->SetXY(136, 27);
	$pdf->MultiCell(66, 6, ((isset($data['p_last_name'])) ? $data['p_last_name'] : ''), $showCellBorder, 'C');

	//DOB
	$pdf->SetXY(32, 33);
	$pdf->MultiCell(8, 6, ((isset($data['p_dob'])) ? substr($data['p_dob'], 0, 2) : ''), $showCellBorder, 'L');
	//
	$pdf->SetXY(45, 33);
	$pdf->MultiCell(8, 6, ((isset($data['p_dob'])) ? substr($data['p_dob'], 3, 2) : ''), $showCellBorder, 'L');
	//
	$pdf->SetXY(58, 33);
	$pdf->MultiCell(19, 6, ((isset($data['p_dob'])) ? substr($data['p_dob'], 6, 4) : ''), $showCellBorder, 'L');

	//Gender - F
	$pdf->SetXY(95, 33);
	$pdf->MultiCell(5, 6, ((isset($data['p_gender']) && $data['p_gender'] == 'F') ? 'x' : ''), $showCellBorder, 'C');
	// - M
	$pdf->SetXY(108, 33);
	$pdf->MultiCell(5, 6, ((isset($data['p_gender']) && $data['p_gender'] == 'M') ? 'x' : ''), $showCellBorder, 'C');

	//SSN
	$pdf->SetXY(142, 33);
	//$pdf->MultiCell(10, 6, ((isset($data['p_ssn'])) ? substr(preg_replace("/[^0-9]/", '', $data['p_ssn']), 0, 3) : ''), $showCellBorder, 'L');
	$pdf->MultiCell(10, 6, ((isset($data['p_ssn'])) ? '***' : ''), $showCellBorder, 'L');
	//
	$pdf->SetXY(156, 33);
	//$pdf->MultiCell(8, 6, ((isset($data['p_ssn'])) ? substr(preg_replace("/[^0-9]/", '', $data['p_ssn']), 3, 2) : ''), $showCellBorder, 'L');
	$pdf->MultiCell(8, 6, ((isset($data['p_ssn'])) ? '**' : ''), $showCellBorder, 'L');
	//
	$pdf->SetXY(168, 33);
	$pdf->MultiCell(19, 6, ((isset($data['p_ssn'])) ? substr(preg_replace("/[^0-9]/", '', $data['p_ssn']), 5, 4) : ''), $showCellBorder, 'L');

	//Address
	$pdf->SetXY(28, 40);
	$pdf->MultiCell(87, 6, ((isset($data['p_address'])) ? $data['p_address'] : ''), $showCellBorder, 'C');

	//City
	$pdf->SetXY(136, 40);
	$pdf->MultiCell(66, 6, ((isset($data['p_city'])) ? $data['p_city'] : ''), $showCellBorder, 'C');

	//State
	$pdf->SetXY(28, 46);
	$pdf->MultiCell(13, 6, ((isset($data['p_state'])) ? $data['p_state'] : ''), $showCellBorder, 'C');

	//ZipCode
	$pdf->SetXY(56, 46);
	$pdf->MultiCell(21, 6, ((isset($data['p_zip'])) ? $data['p_zip'] : ''), $showCellBorder, 'C');

	//Phone
	$pdf->SetXY(92, 46);
	$pdf->MultiCell(11, 6, ((isset($data['p_phone'])) ? substr(preg_replace("/[^0-9]/", '', $data['p_phone']), 0, 3) : ''), $showCellBorder, 'C');
	//
	$pdf->SetXY(104, 46);
	$pdf->MultiCell(12, 6, ((isset($data['p_phone'])) ? substr(preg_replace("/[^0-9]/", '', $data['p_phone']), 3, 3) : ''), $showCellBorder, 'C');
	//
	$pdf->SetXY(120, 46);
	$pdf->MultiCell(20, 6, ((isset($data['p_phone'])) ? substr(preg_replace("/[^0-9]/", '', $data['p_phone']), 6, 4) : ''), $showCellBorder, 'L');

	//Fax
	$pdf->SetXY(153, 46);
	$pdf->MultiCell(11, 6, ((isset($data['p_fax'])) ? substr(preg_replace("/[^0-9]/", '', $data['p_fax']), 0, 3) : ''), $showCellBorder, 'C');
	//
	$pdf->SetXY(165, 46);
	$pdf->MultiCell(12, 6, ((isset($data['p_fax'])) ? substr(preg_replace("/[^0-9]/", '', $data['p_fax']), 3, 3) : ''), $showCellBorder, 'C');
	//
	$pdf->SetXY(181, 46);
	$pdf->MultiCell(20, 6, ((isset($data['p_fax'])) ? substr(preg_replace("/[^0-9]/", '', $data['p_fax']), 6, 4) : ''), $showCellBorder, 'L');

	//E-mail
	$pdf->SetXY(28, 52);
	$pdf->MultiCell(114, 6, ((isset($data['p_email'])) ? $data['p_email'] : ''), $showCellBorder, 'C');

	//No of people in household
	$pdf->SetXY(186, 52);
	$pdf->MultiCell(16, 6, ((isset($data['p_household'])) ? $data['p_household'] : ''), $showCellBorder, 'C');

	//Alternate Contact
	$pdf->SetXY(56, 59);
	$pdf->MultiCell(60, 6, ((isset($data['p_alternate_contact_name'])) ? $data['p_alternate_contact_name'] : ''), $showCellBorder, 'C');

	//Alternate Contact Phone
	$pdf->SetXY(153, 59);
	$pdf->MultiCell(11, 6, ((isset($data['p_alternate_phone'])) ? substr(preg_replace("/[^0-9]/", '', $data['p_alternate_phone']), 0, 3) : ''), $showCellBorder, 'C');
	//
	$pdf->SetXY(165, 59);
	$pdf->MultiCell(12, 6, ((isset($data['p_alternate_phone'])) ? substr(preg_replace("/[^0-9]/", '', $data['p_alternate_phone']), 3, 3) : ''), $showCellBorder, 'C');
	//
	$pdf->SetXY(181, 59);
	$pdf->MultiCell(20, 6, ((isset($data['p_alternate_phone'])) ? substr(preg_replace("/[^0-9]/", '', $data['p_alternate_phone']), 6, 4) : ''), $showCellBorder, 'L');

	//Marital Status - Single
	$pdf->SetXY(34, 66);
	$pdf->MultiCell(5, 6, ((isset($data['p_married']) && $data['p_married'] == 'S') ? 'x' : ''), $showCellBorder, 'C');
	// - Married
	$pdf->SetXY(52, 66);
	$pdf->MultiCell(5, 6, ((isset($data['p_married']) && $data['p_married'] == 'M') ? 'x' : ''), $showCellBorder, 'C');
	// - Divorced
	$pdf->SetXY(71, 66);
	$pdf->MultiCell(5, 6, ((isset($data['p_married']) && $data['p_married'] == 'D') ? 'x' : ''), $showCellBorder, 'C');
	// - Widowed
	$pdf->SetXY(91, 66);
	$pdf->MultiCell(5, 6, ((isset($data['p_married']) && $data['p_married'] == 'W') ? 'x' : ''), $showCellBorder, 'C');

	//Employment Status - Full time
	$pdf->SetXY(134, 66);
	$pdf->MultiCell(5, 6, ((isset($data['p_employment_status']) && $data['p_employment_status'] == 'F') ? 'x' : ''), $showCellBorder, 'C');
	// - Part time
	$pdf->SetXY(154, 66);
	$pdf->MultiCell(5, 6, ((isset($data['p_employment_status']) && $data['p_employment_status'] == 'P') ? 'x' : ''), $showCellBorder, 'C');
	// - Retired
	$pdf->SetXY(171, 66);
	$pdf->MultiCell(5, 6, ((isset($data['p_employment_status']) && $data['p_employment_status'] == 'R') ? 'x' : ''), $showCellBorder, 'C');
	// - Unemployed
	$pdf->SetXY(195, 66);
	$pdf->MultiCell(5, 6, ((isset($data['p_employment_status']) && $data['p_employment_status'] == 'U') ? 'x' : ''), $showCellBorder, 'C');

	//US Citizen - Yes
	$pdf->SetXY(83, 72);
	$pdf->MultiCell(5, 6, ((isset($data['p_uscitizen']) && (bool) $data['p_uscitizen']) ? 'x' : ''), $showCellBorder, 'C');
	// - No
	$pdf->SetXY(96, 72);
	$pdf->MultiCell(5, 6, ((isset($data['p_uscitizen']) && ! (bool) $data['p_uscitizen']) ? 'x' : ''), $showCellBorder, 'C');

	//Disabled - Yes
	$pdf->SetXY(181, 72);
	$pdf->MultiCell(5, 6, ((isset($data['p_disabled_status']) && (bool) $data['p_disabled_status']) ? 'x' : ''), $showCellBorder, 'C');
	// - No
	$pdf->SetXY(193, 72);
	$pdf->MultiCell(5, 6, ((isset($data['p_disabled_status']) && ! (bool) $data['p_disabled_status']) ? 'x' : ''), $showCellBorder, 'C');

	//Medicare - Yes
	$pdf->SetXY(83, 79);
	$pdf->MultiCell(5, 6, ((isset($data['p_medicare']) && (bool) $data['p_medicare']) ? 'x' : ''), $showCellBorder, 'C');
	// - No
	$pdf->SetXY(96, 79);
	$pdf->MultiCell(5, 6, ((isset($data['p_medicare']) && ! (bool) $data['p_medicare']) ? 'x' : ''), $showCellBorder, 'C');

	//Medicare Part D - Yes
	$pdf->SetXY(181, 79);
	$pdf->MultiCell(5, 6, ((isset($data['p_medicare_part_d']) && (bool) $data['p_medicare_part_d']) ? 'x' : ''), $showCellBorder, 'C');
	// - No
	$pdf->SetXY(193, 79);
	$pdf->MultiCell(5, 6, ((isset($data['p_medicare_part_d']) && ! (bool) $data['p_medicare_part_d']) ? 'x' : ''), $showCellBorder, 'C');

	//Medicaid - Yes
	$pdf->SetXY(83, 85);
	$pdf->MultiCell(5, 6, ((isset($data['p_medicaid']) && (bool) $data['p_medicaid']) ? 'x' : ''), $showCellBorder, 'C');
	// - No
	$pdf->SetXY(96, 85);
	$pdf->MultiCell(5, 6, ((isset($data['p_medicaid']) && ! (bool) $data['p_medicaid']) ? 'x' : ''), $showCellBorder, 'C');

	//LIS - Yes
	$pdf->SetXY(181, 85);
	$pdf->MultiCell(5, 6, ((isset($data['p_lis']) && (bool) $data['p_lis']) ? 'x' : ''), $showCellBorder, 'C');
	// - No
	$pdf->SetXY(193, 85);
	$pdf->MultiCell(5, 6, ((isset($data['p_lis']) && ! (bool) $data['p_lis']) ? 'x' : ''), $showCellBorder, 'C');

	//Doctor 1
	if (isset($data['doctors']) && count($data['doctors']) > 0) {
		//- Name
		$pdf->SetXY(30, 103);
		$pdf->MultiCell(75, 6, ((isset($data['doctors'][1]['doctor_first_name']) && isset($data['doctors'][1]['doctor_last_name'])) ? $data['doctors'][1]['doctor_first_name'] . ' ' . $data['doctors'][1]['doctor_last_name'] : ''), $showCellBorder, 'C');
		//- Facility Name
		$pdf->SetXY(30, 109);
		$pdf->MultiCell(75, 6, ((isset($data['doctors'][1]['doctor_facility'])) ? $data['doctors'][1]['doctor_facility'] : ''), $showCellBorder, 'C');
		//- Address / Suite
		$pdf->SetXY(30, 115);
		$pdf->MultiCell(75, 6, ((isset($data['doctors'][1]['doctor_address'])) ? $data['doctors'][1]['doctor_address'] : '') . ((isset($data['doctors'][1]['doctor_address2']) && $data['doctors'][1]['doctor_address2'] != '') ? ', ' . $data['doctors'][1]['doctor_address2'] : ''), $showCellBorder, 'C');
		//- City / State / Zip
		$pdf->SetXY(30, 122);
		$pdf->MultiCell(75, 6, ((isset($data['doctors'][1]['doctor_city'])) ? $data['doctors'][1]['doctor_city'] : '') . ((isset($data['doctors'][1]['doctor_state'])) ? ', ' . $data['doctors'][1]['doctor_state'] : '') . ((isset($data['doctors'][1]['doctor_zip'])) ? ', ' . $data['doctors'][1]['doctor_zip'] : ''), $showCellBorder, 'C');
		//Phone
		$pdf->SetXY(22, 128);
		$pdf->MultiCell(10, 6, ((isset($data['doctors'][1]['doctor_phone'])) ? substr(preg_replace("/[^0-9]/", '', $data['doctors'][1]['doctor_phone']), 0, 3) : ''), $showCellBorder, 'C');
		//
		$pdf->SetXY(32, 128);
		$pdf->MultiCell(10, 6, ((isset($data['doctors'][1]['doctor_phone'])) ? substr(preg_replace("/[^0-9]/", '', $data['doctors'][1]['doctor_phone']), 3, 3) : ''), $showCellBorder, 'C');
		//
		$pdf->SetXY(43, 128);
		$pdf->MultiCell(14, 6, ((isset($data['doctors'][1]['doctor_phone'])) ? substr(preg_replace("/[^0-9]/", '', $data['doctors'][1]['doctor_phone']), 6, 4) : ''), $showCellBorder, 'L');
		//Fax
		$pdf->SetXY(68, 128);
		$pdf->MultiCell(10, 6, ((isset($data['doctors'][1]['doctor_fax'])) ? substr(preg_replace("/[^0-9]/", '', $data['doctors'][1]['doctor_fax']), 0, 3) : ''), $showCellBorder, 'C');
		//
		$pdf->SetXY(78, 128);
		$pdf->MultiCell(10, 6, ((isset($data['doctors'][1]['doctor_fax'])) ? substr(preg_replace("/[^0-9]/", '', $data['doctors'][1]['doctor_fax']), 3, 3) : ''), $showCellBorder, 'C');
		//
		$pdf->SetXY(89, 128);
		$pdf->MultiCell(14, 6, ((isset($data['doctors'][1]['doctor_fax'])) ? substr(preg_replace("/[^0-9]/", '', $data['doctors'][1]['doctor_fax']), 6, 4) : ''), $showCellBorder, 'L');
	}

	//Doctor 2
	if (isset($data['doctors']) && count($data['doctors']) > 1) {
		//- Name
		$pdf->SetXY(127, 103);
		$pdf->MultiCell(75, 6, ((isset($data['doctors'][2]['doctor_first_name']) && isset($data['doctors'][2]['doctor_last_name'])) ? $data['doctors'][2]['doctor_first_name'] . ' ' . $data['doctors'][2]['doctor_last_name'] : ''), $showCellBorder, 'C');
		//- Facility Name
		$pdf->SetXY(127, 109);
		$pdf->MultiCell(75, 6, ((isset($data['doctors'][2]['doctor_facility'])) ? $data['doctors'][2]['doctor_facility'] : ''), $showCellBorder, 'C');
		//- Address / Suite
		$pdf->SetXY(127, 115);
		$pdf->MultiCell(75, 6, ((isset($data['doctors'][2]['doctor_address'])) ? $data['doctors'][2]['doctor_address'] : '') . ((isset($data['doctors'][2]['doctor_address2'])) ? ', ' . $data['doctors'][2]['doctor_address2'] : ''), $showCellBorder, 'C');
		//- City / State / Zip
		$pdf->SetXY(127, 122);
		$pdf->MultiCell(75, 6, ((isset($data['doctors'][2]['doctor_city'])) ? $data['doctors'][2]['doctor_city'] : '') . ((isset($data['doctors'][2]['doctor_state'])) ? ', ' . $data['doctors'][2]['doctor_state'] : '') . ((isset($data['doctors'][2]['doctor_zip'])) ? ', ' . $data['doctors'][2]['doctor_zip'] : ''), $showCellBorder, 'C');
		//Phone
		$pdf->SetXY(120, 128);
		$pdf->MultiCell(10, 6, ((isset($data['doctors'][2]['doctor_phone'])) ? substr(preg_replace("/[^0-9]/", '', $data['doctors'][2]['doctor_phone']), 0, 3) : ''), $showCellBorder, 'C');
		//
		$pdf->SetXY(130, 128);
		$pdf->MultiCell(10, 6, ((isset($data['doctors'][2]['doctor_phone'])) ? substr(preg_replace("/[^0-9]/", '', $data['doctors'][2]['doctor_phone']), 3, 3) : ''), $showCellBorder, 'C');
		//
		$pdf->SetXY(141, 128);
		$pdf->MultiCell(14, 6, ((isset($data['doctors'][2]['doctor_phone'])) ? substr(preg_replace("/[^0-9]/", '', $data['doctors'][2]['doctor_phone']), 6, 4) : ''), $showCellBorder, 'L');
		//Fax
		$pdf->SetXY(165, 128);
		$pdf->MultiCell(10, 6, ((isset($data['doctors'][2]['doctor_fax'])) ? substr(preg_replace("/[^0-9]/", '', $data['doctors'][2]['doctor_fax']), 0, 3) : ''), $showCellBorder, 'C');
		//
		$pdf->SetXY(175, 128);
		$pdf->MultiCell(10, 6, ((isset($data['doctors'][2]['doctor_fax'])) ? substr(preg_replace("/[^0-9]/", '', $data['doctors'][2]['doctor_fax']), 3, 3) : ''), $showCellBorder, 'C');
		//
		$pdf->SetXY(186, 128);
		$pdf->MultiCell(14, 6, ((isset($data['doctors'][2]['doctor_fax'])) ? substr(preg_replace("/[^0-9]/", '', $data['doctors'][2]['doctor_fax']), 6, 4) : ''), $showCellBorder, 'L');
	}

	//Medication
	if (isset($data['medication'])) {
		//for ($i = 0; $i < 8; $i++) {
		foreach ($data['medication'] as $key => $medication) {
			if ($key <= 8) {
				// #1 - Doctor
				$pdf->SetXY(9, 147 + (($key - 1) * 5));
				$pdf->MultiCell(26, 5, $medication['medication_doctor'], $showCellBorder, 'C');
				// #1 - Medication
				$pdf->SetXY(36, 147 + (($key - 1) * 5));
				$pdf->MultiCell(69, 5, $medication['medication_name'], $showCellBorder, 'C');
				// #1 - Strength
				$pdf->SetXY(106, 147 + (($key - 1) * 5));
				$pdf->MultiCell(21, 5, $medication['medication_strength'], $showCellBorder, 'C');
				// #1 - Frequency
				$pdf->SetXY(128, 147 + (($key - 1) * 5));
				$pdf->MultiCell(74, 5, $medication['medication_frequency'], $showCellBorder, 'C');
			}
		}
	}

	//Salary
	$pdf->SetXY(44, 192);
	$pdf->MultiCell(30, 6, ((isset($data['p_income_salary'])) ? sprintf('%0.2f', preg_replace("/[^0-9.]/", "", $data['p_income_salary'])) : ''), $showCellBorder, 'C');

	//Pension
	$pdf->SetXY(111, 192);
	$pdf->MultiCell(30, 6, ((isset($data['p_income_pension'])) ? sprintf('%0.2f', preg_replace("/[^0-9.]/", "", $data['p_income_pension'])) : ''), $showCellBorder, 'C');

	//SS Retirement
	$pdf->SetXY(177, 192);
	$pdf->MultiCell(25, 6, ((isset($data['p_income_ss_retirement'])) ? sprintf('%0.2f', preg_replace("/[^0-9.]/", "", $data['p_income_ss_retirement'])) : ''), $showCellBorder, 'C');

	//Unemployment
	$pdf->SetXY(44, 199);
	$pdf->MultiCell(30, 6, ((isset($data['p_income_unemployment'])) ? sprintf('%0.2f', preg_replace("/[^0-9.]/", "", $data['p_income_unemployment'])) : ''), $showCellBorder, 'C');

	//Annuity
	$pdf->SetXY(111, 199);
	$pdf->MultiCell(30, 6, ((isset($data['p_income_annuity'])) ? sprintf('%0.2f', preg_replace("/[^0-9.]/", "", $data['p_income_annuity'])) : ''), $showCellBorder, 'C');

	//SS Disability
	$pdf->SetXY(177, 199);
	$pdf->MultiCell(25, 6, ((isset($data['p_income_ss_disability'])) ? sprintf('%0.2f', preg_replace("/[^0-9.]/", "", $data['p_income_ss_disability'])) : ''), $showCellBorder, 'C');

	$pdf->AddFont('DancingScript-Bold', '', 'DancingScript-Bold.php');
	$pdf->SetFont('DancingScript-Bold', '', 12);

	//No Income
	$pdf->SetXY(44, 216);
	$pdf->MultiCell(60, 6, ((isset($data['p_income_zero']) && (bool) $data['p_income_zero']) ? $data['p_first_name'] . ' ' . $data['p_last_name'] : ''), $showCellBorder, 'C');

	//No Tax Filing
	$pdf->SetXY(140, 199);
	$pdf->MultiCell(60, 6, ((isset($data['p_income_file_tax_return']) && (bool) $data['p_income_file_tax_return']) ? $data['p_first_name'] . ' ' . $data['p_last_name'] : ''), $showCellBorder, 'C');

	$pdf->SetFont('Helvetica', '', 12);

	//Checking Account
	$pdf->SetXY(34, 236);
	$pdf->MultiCell(5, 5, ((isset($data['p_payment_method']) && $data['p_payment_method'] == 'ach') ? 'X' : ''), $showCellBorder, 'C');

	//Credit Card
	if (isset($data['p_payment_method']) && $data['p_payment_method'] == 'cc') {
		// - VISA
		$pdf->SetXY(90, 235);
		$pdf->MultiCell(5, 5, ((isset($data['p_cc_type']) && $data['p_cc_type'] == 'Visa') ? 'X' : ''), $showCellBorder, 'C');
		// - Mastercard
		$pdf->SetXY(90, 243);
		$pdf->MultiCell(5, 5, ((isset($data['p_cc_type']) && $data['p_cc_type'] == 'Mastercard') ? 'X' : ''), $showCellBorder, 'C');

		// - CC No #1
		$cc_no = sprintf('%016s', ((isset($data['p_cc_number'])) ? $data['p_cc_number'] : ''));
		for ($i = 0; $i < 16; $i++){
			$pdf->SetXY(98 + ($i * 5.6), 236);
			$pdf->MultiCell(5, 6, ($i >= 12) ? $cc_no[$i] : '*', $showCellBorder, 'C');
		}
		// - CC Exp Month
		//$cc_exp_mo = sprintf('%02s', ((isset($data['p_cc_exp_month'])) ? $data['p_cc_exp_month'] : ''));
		//$pdf->SetXY(120, 243);
		//$pdf->MultiCell(6, 6, $cc_exp_mo[0], $showCellBorder, 'C');
		//$pdf->SetXY(126, 243);
		//$pdf->MultiCell(6, 6, $cc_exp_mo[1], $showCellBorder, 'C');

		// - CC Exp Year
		//$cc_exp_year = ((isset($data['p_cc_exp_year'])) ? substr($data['p_cc_exp_year'], strlen($data['p_cc_exp_year']) - 2) : '');
		//$pdf->SetXY(137, 243);
		//$pdf->MultiCell(6, 6, $cc_exp_year[0], $showCellBorder, 'C');
		//$pdf->SetXY(143, 243);
		//$pdf->MultiCell(6, 6, $cc_exp_year[1], $showCellBorder, 'C');

		// - CC CVV
		//$pdf->SetXY(159, 243);
		//$pdf->MultiCell(6, 6, $data['p_cc_cvv'][0], $showCellBorder, 'C');

		//$pdf->SetXY(165, 243);
		//$pdf->MultiCell(6, 6, $data['p_cc_cvv'][1], $showCellBorder, 'C');

		//$pdf->SetXY(171, 243);
		//$pdf->MultiCell(6, 6, $data['p_cc_cvv'][2], $showCellBorder, 'C');
	}

	// - How did you heard about us
	$pdf->SetXY(90, 253);
	$pdf->MultiCell(111, 6, ((isset($data['p_hear_about'])) ? $data['p_hear_about'] : ''), $showCellBorder, 'C');

	//
	//PAGE 2
	//

	$tplIdx = $pdf->importPage(2, '/MediaBox');
	$pdf->addPage();
	$pdf->useTemplate($tplIdx, 0, 0, 210);

	//Keep for you records.
	$pdf->Image('images/keep.png', 10, 10, 0, 0);

	//Date
	$pdf->SetXY(21, 241);
	$pdf->MultiCell(28, 5, date('m/d/Y'), $showCellBorder, 'C');

	$pdf->AddFont('DancingScript-Bold', '', 'DancingScript-Bold.php');
	$pdf->SetFont('DancingScript-Bold', '', 12);

	$patient_initials = $data['p_first_name'][0] . ' ' . $data['p_last_name'][0];

	//#1 Signature
	$pdf->SetXY(192, 92);
	$pdf->MultiCell(10, 5, ((isset($data['p_payment_agreement']) && (bool) $data['p_payment_agreement']) ? $patient_initials : ''), $showCellBorder, 'C');

	//#2 Signature
	$pdf->SetXY(192, 154);
	$pdf->MultiCell(10, 5, ((isset($data['p_service_agreement']) && (bool) $data['p_service_agreement']) ? $patient_initials : ''), $showCellBorder, 'C');

	//#3 Signature
	$pdf->SetXY(192, 204);
	$pdf->MultiCell(10, 5, ((isset($data['p_guaranty_agreement']) && (bool) $data['p_guaranty_agreement']) ? $patient_initials : ''), $showCellBorder, 'C');

	//#4 Signature
	$pdf->SetXY(37, 232);
	$pdf->MultiCell(60, 6, $data['p_first_name'] . ' ' . $data['p_last_name'], $showCellBorder, 'C');

	$rs = $pdf->Output('Prescription_Hope_Application.pdf', $output);

	if ($output == 'S') {
		return $rs;
	}
}
