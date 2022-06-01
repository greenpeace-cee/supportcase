# supportcase
### Short overview:
The extension helps to manage CiviCRM cases.
The cases can be used like a group of mailing messages.
User can send/reply/reply/forward emails and send attachments.

Dashboard page:

![Screenshot](/images/Dashboard/DashboardWithOpenFilters.png)

Manage case page:

![Screenshot](/images/ManageCase/ManageCase.png)

## Requirements

* PHP v7.3+
* CiviCRM  5.39.0+
* CiviCRM extension:
  * at.greenpeace.casetools
  * mailutils
* CiviCRM optional extension:
  * org.civicrm.shoreditch - it makes better view and reduces visual issues.

## Usage

### Dashboard page
![Screenshot](/images/Dashboard/Dashboard.png)

---

At the top of page is located filter fields.
As default shows only cases with statuses: `ongoing` and `urgent`.
Can be filtered by:
* `Keyword` - search this string in `subject` or related email messages
* `Case Status`
* `Involved Agent(s)`
* `Show deleted cases?`
* `Case Start/End Date`
* `Client(s)`
* `Tags`
* `case id` - in this way all another filters will be ignored

---

At the bottom of page is located result of search:

![Screenshot](/images/Dashboard/SearchResult.png)

The result of search divided by tabs.
The first tab `all` is includes all cases from result of search.
The second tab `My cases` is includes all cases from result of search which are related to current user. 
Another tabs it is custom tabs. Those tabs are CiviCRM case categories, and can be added/edited/deleted.

---

Panel of actions locates over the tabs:

![Screenshot](/images/Dashboard/DashboardActionsPanel.png)

In this panel user can:
* clean filters
* add new support case - redirects to `Create Support Case` page
* quick actions with selected cases:
  * Change category to another one
  * report as spam - set status to `spam`
  * resolve case - set status to `complated`
  * delete case

---

Block editing one case by 2 different users on the same time:

![Screenshot](/images/Dashboard/LockCase.png)
If user is editing some case. This case will be locked for another users.
It prevents data conflicts while saving the case.

Also, user can unlock the locked case. 
This ability needs when some user accidentally lock the case for a long time. 
Ex: user didn't close the tab. 

---

### manage case page
![Screenshot](/images/ManageCase/WithClosedColapses.png)

---

At the top of page located buttons with fast actions:
![Screenshot](/images/ManageCase/TopButtons.png)

---

Under buttons located main info about case:

![Screenshot](/images/ManageCase/CaseMainFields.png)

This all this fields can be edited:

![Screenshot](/images/ManageCase/EditCaseMainFields.png)

---

User can add/delete/edit comments to the case:

![Screenshot](/images/ManageCase/Comments.png)

---

Communication block:

User can send new email, reply email, reply all email, forward email.

![Screenshot](/images/ManageCase/SendNewMessage.png)

![Screenshot](/images/ManageCase/MessageAfterSending.png)

---

Quick actions:

![Screenshot](/images/ManageCase/QuickActions.png)

---

Case activities:
![Screenshot](/images/ManageCase/CaseActivities.png)

---

Resent cases:

![Screenshot](/images/ManageCase/ResentCases.png)

## Installation

* Install CiviCRM 5.39.0+
* Install CiviCRM extension:
  * `mailutils`
  * `at.greenpeace.casetools`
  * `org.civicrm.shoreditch`
* enable `supportcase` extension

## Known Issues
