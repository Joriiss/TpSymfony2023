<?php
namespace App\Components;

use App\Entity\Student;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('student')]
class StudentComponent
{
    public Student $student;
}