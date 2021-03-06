<?php

namespace Intern;

require_once PHPWS_SOURCE_DIR . 'mod/intern/vendor/autoload.php';

/**
 * InternshipContractPdfView
 *
 * View class for generating a PDF of an internship.
 *
 * @author jbooker
 * @package Intern
 */

class InternshipContractPdfView {

    private $internship;
    private $emergencyContacts;

    private $pdf;

    /**
     * Creates a new InternshipContractPdfView
     *
     * @param Internship $i
     * @param Array<EmergencyContact> $emergencyContacts
     */
    public function __construct(Internship $i, Array $emergencyContacts)
    {
        $this->internship = $i;
        $this->emergencyContacts = $emergencyContacts;

        $this->generatePdf();
    }

    /**
     * Returns the FPDI (FPDF) object which was generated by this view.
     *
     * @return FPDI
     */
    public function getPdf()
    {
        return $this->pdf;
    }

    /**
     * Does the hard work of generating a PDF.
     */
    private function generatePdf()
    {
        $this->pdf = new \FPDI('P', 'mm', 'Letter');
        $a = $this->internship->getAgency();
        $d = $this->internship->getDepartment();
        $f = $this->internship->getFaculty();
        $m = $this->internship->getUgradMajor();
        $g = $this->internship->getGradProgram();
        //$subject = $this->internship->getSubject();

        $pagecount = $this->pdf->setSourceFile(PHPWS_SOURCE_DIR . 'mod/intern/pdf/AppStateInternship_Contract_StartEndDates-flat.pdf');
        $tplidx = $this->pdf->importPage(1);
        $this->pdf->addPage();
        $this->pdf->useTemplate($tplidx);

        $this->pdf->setFont('Times', null, 10);
        $this->pdf->setAutoPageBreak(true, 0);

        /**************************
         * Internship information *
        */

        /* Department */
        $this->pdf->setXY(138, 40);
        $this->pdf->multiCell(73, 3, $d->getName());

        /* Course title */
        $this->pdf->setXY(138, 43);
        $this->pdf->cell(73, 6, $this->internship->getCourseTitle());

        /* Term */
        $this->pdf->setXY(138, 48);
        $this->pdf->cell(73, 6, Term::rawToRead($this->internship->getTerm()));

        /* Location */
        if($this->internship->isDomestic()){
            $this->pdf->setXY(85, 68);
            $this->pdf->cell(12, 5, 'X');
        }
        if($this->internship->isInternational()){
            $this->pdf->setXY(168, 68);
            $this->pdf->cell(12, 5, 'X');
        }

        /**
         * Student information.
         */
        $this->pdf->setXY(40, 84);
        $this->pdf->cell(55, 5, $this->internship->getFullName());

        $this->pdf->setXY(155, 84);
        $this->pdf->cell(42, 5, $this->internship->getBannerId());

        $this->pdf->setXY(41, 94);
        $this->pdf->cell(54, 5, $this->internship->getEmailAddress() . '@appstate.edu');

        $this->pdf->setXY(125, 94);
        $this->pdf->cell(54, 5, $this->internship->getPhoneNumber());

        /* Student Address */
        $this->pdf->setXY(60, 89);
        $this->pdf->cell(54, 5, $this->internship->getStudentAddress());


        /* Payment */
        if($this->internship->isPaid()){
            $this->pdf->setXY(25, 99);
            $this->pdf->cell(10,5, 'X');
        }else {
            $this->pdf->setXY(87, 99);
            $this->pdf->cell(10,5,'X');
        }

        // Stipend
        if($this->internship->hasStipend()) {
            $this->pdf->setXY(56, 99);
            $this->pdf->cell(10,5, 'X');
        }

        /* Start/end dates */
        $this->pdf->setXY(50, 106);
        $this->pdf->cell(25, 5, $this->internship->getStartDate(true));
        $this->pdf->setXY(114, 106);
        $this->pdf->cell(25, 5, $this->internship->getEndDate(true));

        /* Hours */
        $this->pdf->setXY(190, 100);
        $this->pdf->cell(12, 5, $this->internship->getCreditHours());

        // Hours per week
        $this->pdf->setXY(147, 100);
        $this->pdf->cell(12, 5, $this->internship->getAvgHoursPerWeek());

        /***
         * Faculty supervisor information.
         */
        if(isset($f)){
            $this->pdf->setXY(26, 119);
            $this->pdf->cell(81, 5, $f->getFullName());

            $this->pdf->setXY(29, 126);
            $this->pdf->cell(81, 5, $f->getStreetAddress1());

            $this->pdf->setXY(15, 133);
            $this->pdf->cell(81, 5, $f->getStreetAddress2());

            $this->pdf->setXY(60, 133);
            $this->pdf->cell(81, 5, $f->getCity());

            $this->pdf->setXY(88, 133);
            $this->pdf->cell(81, 5, $f->getState());

            $this->pdf->setXY(95, 133);
            $this->pdf->cell(81, 5, $f->getZip());

            $this->pdf->setXY(26, 140);
            $this->pdf->cell(77, 5, $f->getPhone());

            $this->pdf->setXY(25, 148);
            $this->pdf->cell(77, 5, $f->getFax());

            $this->pdf->setXY(26, 154);
            $this->pdf->cell(77, 5, $f->getUsername() . '@appstate.edu');
        }

        /***
         * Agency information.
        */
        $this->pdf->setXY(135, 117);
        $this->pdf->cell(71, 5, $a->getName());

        $agency_address = $a->getStreetAddress();

        //TODO: make this smarter so it adds the line break between words
        if(strlen($agency_address) < 49){
            // If it's short enough, just write it
            $this->pdf->setXY(126, 122);
            $this->pdf->cell(77, 5, $agency_address);
        }else{
            // Too long, need to use two lines
            $agencyLine1 = substr($agency_address, 0, 49); // get first 50 chars
            $agencyLine2 = substr($agency_address, 49); // get the rest, hope it fits

            $this->pdf->setXY(125, 121);
            $this->pdf->cell(77, 5, $agencyLine1);
            $this->pdf->setXY(110, 126);
            $this->pdf->cell(77, 5, $agencyLine2);
        }

        /**
         * Agency supervisor info.
         */
        $this->pdf->setXY(110, 138);
        $super = "";
        $superName = $a->getSupervisorFullName();
        if(isset($superName) && !empty($superName) && $superName != ''){
            //test('ohh hai',1);
            $super .= $a->getSupervisorFullName();
        }

        $supervisorTitle = $a->getSupervisorTitle();

        if(isset($a->supervisor_title) && !empty($a->supervisor_title)){
            $super .= ', ' . $supervisorTitle;
        }
        $this->pdf->cell(75, 5, $super);

        $this->pdf->setXY(124, 143);
        $this->pdf->cell(78, 5, $a->getSuperAddress());

        $this->pdf->setXY(123, 159);
        $this->pdf->cell(72, 5, $a->getSupervisorEmail());

        $this->pdf->setXY(124, 154);
        $this->pdf->cell(33, 5, $a->getSupervisorPhoneNumber());

        $this->pdf->setXY(165, 154);
        $this->pdf->cell(40, 5, $a->getSupervisorFaxNumber());

        /* Internship Location */
        $internshipAddress = trim($this->internship->getStreetAddress());
        $agencyAddress = trim($a->getStreetAddress());

        if($internshipAddress != '' && $agencyAddress != '' && $internshipAddress != $agencyAddress) {
            $this->pdf->setXY(110, 169);
            $this->pdf->cell(52, 5, $this->internship->getLocationAddress());
        }


        /**********
         * Page 2 *
        **********/
        $tplidx = $this->pdf->importPage(2);
        $this->pdf->addPage();
        $this->pdf->useTemplate($tplidx);

        /* Emergency Contact Info */
        if(sizeof($this->emergencyContacts) > 0){
            $firstContact = $this->emergencyContacts[0];

            $this->pdf->setXY(60, 266);
            $this->pdf->cell(52, 0, $firstContact->getName());

            $this->pdf->setXY(134, 266);
            $this->pdf->cell(52, 0, $firstContact->getRelation());

            $this->pdf->setXY(175, 266);
            $this->pdf->cell(52, 0, $firstContact->getPhone());
        }
    }
}
