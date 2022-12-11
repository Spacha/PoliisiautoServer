<?php

/**
 * Copyright (c) 2022, Miika Sikala, Essi Passoja, Lauri Klemettilä
 *
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

use App\Models\{
    ReportMessage,
    Organization,
    ReportCase,
    Report,
    Student,
    Teacher,
    User,
};

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        ////////////////////////////////////////////////////////////////////////
        // Auth Gates
        ////////////////////////////////////////////////////////////////////////

        // Only self can access their profile
        Gate::define('show-profile', function (User $user, User $profile) {
            return $user->id == $profile->id;
        });

        // A member of organization can show organization
        Gate::define('show-profile-organization', function (User $user, Organization $organization) {
            return $user->organization_id == $organization->id;
        });

        ////////////////////////////////////////////////////////////////////////
        // Student Gates
        ////////////////////////////////////////////////////////////////////////

        // Anyone in the organization can list the students
        // FIXME: Only teachers should be able to. Students should be able to
        //        get a list of students' names and IDs but nothing else!
        Gate::define('list-students', function (User $user, Organization $organization) {
            return $user->organization_id == $organization->id;
        });

        // Self AND teachers of the organization can show a student
        Gate::define('show-student', function (User $user, Student $student) {
            return ($user->id == $student->id) ||
                   ($user->isTeacher() && $user->organization_id == $student->organization_id);
        });

        // Only self can update student
        Gate::define('update-student', function (User $user, Student $student) {
            return $user->id == $student->id;
        });

        // Only self can delete student
        Gate::define('delete-student', function (User $user, Student $student) {
            return $user->id == $student->id;
        });

        // Self AND teachers of the organization can list a student's reports
        Gate::define('list-student-reports', function (User $user, Student $student) {
            return ($user->id == $student->id) ||
                   ($user->isTeacher() && $user->organization_id == $student->organization_id);
        });

        // Only teachers of the organization can list a student's involved reports
        Gate::define('list-student-involved-reports', function (User $user, Student $student) {
            return $user->isTeacher() && $user->organization_id == $student->organization_id;
        });

        ////////////////////////////////////////////////////////////////////////
        // Teacher Gates
        ////////////////////////////////////////////////////////////////////////

        // Anyone in the organization can list the teachers
        // FIXME: Only teachers should be able to. Students should be able to
        //        get a list of teacehrs' names and IDs but nothing else (probably)!
        Gate::define('list-teachers', function (User $user, Organization $organization) {
            return $user->organization_id == $organization->id;
        });

        // Anyone in the organization can show a teacher
        Gate::define('show-teacher', function (User $user, Teacher $teacher) {
            return $user->organization_id == $teacher->organization_id;
        });

        // Only self can update teacher
        Gate::define('update-teacher', function (User $user, Teacher $teacher) {
            return $user->id == $teacher->id;
        });

        // Only self can delete teacher
        Gate::define('delete-teacher', function (User $user, Teacher $teacher) {
            return $user->id == $teacher->id;
        });

        // Only self can list teacher's reports
        Gate::define('list-teacher-reports', function (User $user, Teacher $teacher) {
            return $user->id == $teacher->id;
        });

        // Only self can list teacher's assigned reports
        Gate::define('list-teacher-assigned-reports', function (User $user, Teacher $teacher) {
            return $user->id == $teacher->id;
        });

        ////////////////////////////////////////////////////////////////////////
        // Organization Gates
        ////////////////////////////////////////////////////////////////////////

        // Nobody at the moment can list organizations (TODO)
        Gate::define('list-organizations', function (User $user) {
            return false;
        });

        // Nobody at the moment can create organizations (TODO)
        Gate::define('create-organizations', function (User $user) {
            return false;
        });

        // Only members of an organization can view it.
        Gate::define('show-organization', function (User $user, $organization) {
            return $user->organization_id == $organization->id;
        });

        // Only administrators of the organization can update it.
        Gate::define('update-organization', function (User $user, $organization) {
            return $user->isAdministrator() && $user->organization_id == $organization->id;
        });

        // Nobody at the moment can delete organizations (TODO)
        Gate::define('delete-organization', function (User $user, $organization) {
            return false;
        });

        ////////////////////////////////////////////////////////////////////////
        // Report Case Gates
        ////////////////////////////////////////////////////////////////////////

        // Only teachers of the organization can view its cases.
        Gate::define('list-report-cases', function (User $user, Organization $organization) {
            return $user->isTeacher() && $user->organization_id == $organization->id;
        });

        // Only members of the organization can store cases to it.
        Gate::define('create-report-case', function (User $user, Organization $organization) {
            return $user->organization_id == $organization->id;
        });

        // Only teachers of the organization can view a case.
        Gate::define('show-report-case', function (User $user, ReportCase $case) {
            return $user->isTeacher() && $user->organization_id == $case->organization_id;
        });

        // Only teachers of the organization can update a case.
        Gate::define('update-report-case', function (User $user, ReportCase $case) {
            return $user->isTeacher() && $user->organization_id == $case->organization_id;
        });

        // Only teachers of the organization can delete a case.
        Gate::define('delete-report-case', function (User $user, ReportCase $case) {
            return $user->isTeacher() && $user->organization_id == $case->organization_id;
        });

        // Only teachers of the organization can list reports in a case.
        Gate::define('list-reports-in-case', function (User $user, ReportCase $case) {
            return $user->isTeacher() && $user->organization_id == $case->organization_id;
        });

        ////////////////////////////////////////////////////////////////////////
        // Report Gates
        ////////////////////////////////////////////////////////////////////////

        // Only teachers of the organization can view its reports.
        Gate::define('list-reports', function (User $user, Organization $organization) {
            return $user->isTeacher() && $user->organization_id == $organization->id;
        });

        // Only members of the organization can store reports to it.
        // TODO: Case should have owner and only its owner can store reports to it!
        Gate::define('create-report', function (User $user, ReportCase $case) {
            return $user->organization_id == $case->organization_id;
        });

        // Only teachers of the organization AND the reporter can view the report.
        Gate::define('show-report', function (User $user, Report $report) {
            return ($user->id == $report->reporter_id) ||
                   ($user->isTeacher() && $user->organization_id == $report->case->organization_id);
        });

        // Only the reporter can update a report
        Gate::define('update-report', function (User $user, Report $report) {
            return $user->id == $report->reporter_id;
        });

        // Only the reporter can delete a report
        Gate::define('delete-report', function (User $user, Report $report) {
            return $user->id == $report->reporter_id;
        });

        // Only teachers of the organization AND the reporter can list the report's messages.
        Gate::define('list-report-messages', function (User $user, Report $report) {
            return ($user->id == $report->reporter_id) ||
                   ($user->isTeacher() && $user->organization_id == $report->case->organization_id);
        });

        // Only teachers of the organization can update a case of a report.
        Gate::define('update-case-of-report', function (User $user, Report $report) {
            return $user->isTeacher() && $user->organization_id == $report->case->organization_id;
        });

        // Only members of the organization can store reports and cases to it.
        Gate::define('create-report-and-case', function (User $user, Organization $organization) {
            return $user->organization_id == $organization->id;
        });

        ////////////////////////////////////////////////////////////////////////
        // Report Message Gates
        ////////////////////////////////////////////////////////////////////////

        // Only teachers of the organization AND the reporter can store messages to the report
        Gate::define('create-report-message', function (User $user, Report $report) {
            return ($user->id == $report->reporter_id) ||
                   ($user->isTeacher() && $user->organization_id == $report->case->organization_id);
        });

        // Only teachers of the organization AND the author can view a message
        Gate::define('show-report-message', function (User $user, ReportMessage $message) {
            return ($user->id == $message->author_id) ||
                   ($user->isTeacher() && $user->organization_id == $message->report->case->organization_id);
        });

        // Only the message author can update it.
        Gate::define('update-report-message', function (User $user, ReportMessage $message) {
            return $user->id == $message->author_id;
        });

        // Only the message author can delete it.
        Gate::define('delete-report-message', function (User $user, ReportMessage $message) {
            return $user->id == $message->author_id;
        });
    }
}
