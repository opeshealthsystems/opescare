<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PublicPageController extends Controller
{
    public function index()
    {
        return view('public.landing');
    }

    public function about()
    {
        return view('public.about');
    }

    public function howItWorks()
    {
        return view('public.how_it_works');
    }

    public function solutionsPatients()
    {
        return view('public.solutions.patients');
    }

    public function solutionsHospitals()
    {
        return view('public.solutions.hospitals');
    }

    public function solutionsPharmacies()
    {
        return view('public.solutions.pharmacies');
    }

    public function solutionsLaboratories()
    {
        return view('public.solutions.laboratories');
    }

    public function solutionsInsurers()
    {
        return view('public.solutions.insurers');
    }

    public function solutionsPublicHealth()
    {
        return view('public.solutions.public_health');
    }

    public function interoperability()
    {
        return view('public.interoperability');
    }

    public function developers()
    {
        return view('public.developers');
    }

    public function security()
    {
        return view('public.security');
    }

    public function privacy()
    {
        return view('public.privacy');
    }

    public function terms()
    {
        return view('public.terms');
    }

    public function consent()
    {
        return view('public.consent');
    }

    public function faq()
    {
        return view('public.faq');
    }

    public function help()
    {
        return view('public.help');
    }

    public function contact()
    {
        return view('public.contact');
    }

    public function status()
    {
        return view('public.status');
    }

    public function showRegisterSelector()
    {
        return view('auth.register');
    }

    public function showPatientRegister()
    {
        return view('auth.register.patient');
    }

    public function showHospitalRegister()
    {
        return view('auth.register.hospital');
    }
}
