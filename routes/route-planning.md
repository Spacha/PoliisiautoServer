# Organizations:
    List all:
        GET: organizations
    Show:
        GET: organizations/{id}
    Store new:
        POST:  organizations
    Update:
        PATCH: organizations/{id}
    Destroy:
        DELETE: organizations/{id}
# Cases:
    List all in organization:
        GET: organizations/{id}/cases
    List all student is involved in:
        GET: students/{id}/cases
    List all assigned to a teacher:
        GET: teachers/{id}/cases
    Show:
        GET: cases/{id}
    Store new to organization:
        POST: organizations/{id}/cases
    Update:
        PATCH: cases/{id}
    Destroy:
        DELETE: cases/{id}
# Reports (incidents/entries):
    List all in organization:
        GET: organizations/{id}/reports
    List all in case:
        GET: cases/{id}/reports
    List all student is involved in (bully or bullied):
        GET: students/{id}/reports
    List all assigned to a teacher:
        GET: teachers/{id}/reports
    Show:
        GET: reports/{id}
    Store new to case:
        POST: cases/{id}/reports
    Update:
        PATCH: reports/{id}
    Destroy:
        DELETE: reports/{id}