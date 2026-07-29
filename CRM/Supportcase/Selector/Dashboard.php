<?php

class CRM_Supportcase_Selector_Dashboard extends CRM_Core_Selector_Base {

  /**
   * This defines two actions- View and Edit.
   *
   * @var array
   */
  public static $_links = NULL;

  /**
   * The action links that we need to display for the browse screen.
   *
   * @var array
   */
  private static $_actionLinks;

  /**
   * We use desc to remind us what that column is, name is used in the tpl
   *
   * @var array
   */
  public static $_columnHeaders;

  /**
   * Properties of contact we're interested in displaying
   * @var array
   */
  public static $_properties = [
    'contact_id',
    'contact_type',
    'sort_name',
    'phone',
    'display_name',
    'case_id',
    'case_subject',
    'case_status_id',
    'case_status',
    'case_role',
  ];

  /**
   * Are we restricting ourselves to a single contact
   *
   * @var bool
   */
  protected $_single = FALSE;

  /**
   * Are we restricting ourselves to a single contact
   *
   * @var bool
   */
  protected $_limit = NULL;

  /**
   * What context are we being invoked from
   *
   * @var string
   */
  protected $_context = NULL;

  /**
   * QueryParams is the array returned by exportValues called on
   * the HTML_QuickForm_Controller for that page.
   *
   * @var array
   */
  public $_queryParams;

  /**
   * Represent the type of selector
   *
   * @var int
   */
  protected $_action;

  /**
   * The additional clause that we restrict the search with
   *
   * @var string
   */
  protected $_additionalClause = NULL;

  /**
   * The query object
   *
   * @var string
   */
  protected $_query;

  /**
   * Case all available tags
   *
   * @var string
   */
  protected $_caseAllTags;

  /**
   * Class constructor.
   *
   * @param array $queryParams
   *   Array of parameters for query.
   * @param int $action - action of search basic or advanced. It const from CRM_Core_Action
   * @param string $additionalClause
   *   If the caller wants to further restrict the search (used in participations).
   * @param bool $single
   *   Are we dealing only with one contact?.
   * @param int $limit
   *   How many signers do we want returned.
   *
   * @param string $context
   * @throws CRM_Core_Exception
   */
  public function __construct(&$queryParams, $action = CRM_Core_Action::NONE, $additionalClause = NULL, $single = FALSE, $limit = NULL, $context = 'search') {
    $params = [];
    foreach ($queryParams as $queryParam) {
      if (!empty($queryParam[0]) && !empty($queryParam[2])) {
        $params[$queryParam[0]] = $queryParam;
      }
    }

    // if 'case_id' is set then ignore all other params
    $isSearchByCaseId = isset($params['case_id']);
    $this->_queryParams = $isSearchByCaseId ? [$params['case_id']] : $queryParams;

    $this->_single = $single;
    $this->_limit = $limit;
    $this->_context = $context;
    $this->_additionalClause = $additionalClause;
    $this->_action = $action;
    $this->_query = new CRM_Contact_BAO_Query($this->_queryParams, $this->getReturnFields(), NULL, FALSE, FALSE, CRM_Contact_BAO_Query::MODE_CASE);
    $this->_query->_distinctComponentClause = " civicrm_case.id ";
    $this->_query->_groupByComponentClause = " GROUP BY civicrm_case.id ";
    $this->_caseAllTags = CRM_Supportcase_Utils_Tags::getAvailableTags('civicrm_case');
    $commActivityTypes = CRM_Supportcase_Utils_Setting::getAvailableActivityTypeIds();
    if (!empty($commActivityTypes)) {
      $selectRecentComm = CRM_Core_DAO::composeQuery("(
      SELECT
          MAX(activity_date_time)
      FROM
          civicrm_case_activity
      INNER JOIN
          civicrm_activity case_activity
      ON
          civicrm_case_activity.activity_id = case_activity.id AND
          case_activity.is_current_revision = 1
          AND case_activity.activity_type_id IN (%1)
      WHERE
          civicrm_case_activity.case_id = civicrm_case.id
      ) AS most_recent_communication", [
        1 => [implode(',', $commActivityTypes), 'CommaSeparatedIntegers'],
      ]);
    }
    else {
      $selectRecentComm = 'case_activity.activity_date_time AS most_recent_communication';
    }
    $this->_query->_select['most_recent_communication'] = $selectRecentComm;

    if ($isSearchByCaseId) {
      return;
    }

    if (!isset($params['is_show_deleted_cases'])) {
      $this->addNewWhere(' civicrm_case.is_deleted <> 1 ');
    }

    if (isset($params['case_keyword'])) {
      $keyWord = (is_array($params['case_keyword'][2])) ? $params['case_keyword'][2]['LIKE'] : '%' . $params['case_keyword'][2] . '%' ;
      $where = CRM_Core_DAO::composeQuery(" (case_activity.subject  LIKE %1 OR case_activity.details LIKE %1 ) ", [
        1 => [$keyWord , 'String'],
      ]);
      $this->addNewWhere($where);
    }

    if (isset($params['case_agents'])) {
      $whereTable = "\n LEFT JOIN civicrm_activity_contact ON (civicrm_activity_contact.activity_id = civicrm_case_activity.activity_id) ";
      $this->_query->_whereTables['civicrm_activity_contact'] = $whereTable;
      $this->_query->_tables['civicrm_activity_contact'] = $whereTable;

      $where = CRM_Core_DAO::composeQuery(" civicrm_activity_contact.contact_id IN(%1) AND civicrm_activity_contact.record_type_id = %2 ", [
        1 => [$params['case_agents'][2] , 'CommaSeparatedIntegers'],
        2 => [2 , 'Integer'], // 1 assignee, 2 creator, 3 focus or target
      ]);
      $this->addNewWhere($where);
    }

    if (isset($params['case_client'])) {
      $where = CRM_Core_DAO::composeQuery(" civicrm_case_contact.contact_id IN(%1) ", [
        1 => [$params['case_client'][2] , 'CommaSeparatedIntegers'],
      ]);
      $this->addNewWhere($where);
    }
  }

  /**
   * Adds new where to the query object
   * @param $where
   */
  private function addNewWhere($where) {
    $this->_query->_where[0][] = $where;
    $this->_query->_whereClause = (empty($this->_query->_whereClause)) ? $where : $this->_query->_whereClause . ' AND ' . $where;
  }

  /**
   * Gets list of 'return fields' for query
   *
   * @return array
   */
  private function getReturnFields() {
    $returnFields = CRM_Case_BAO_Query::defaultReturnProperties(CRM_Contact_BAO_Query::MODE_CASE, FALSE);
    try {
      $categoryCustomFieldName = CRM_Core_BAO_CustomField::getCustomFieldID('category', CRM_Supportcase_Install_Entity_CustomGroup::CASE_DETAILS, TRUE);
      if (!empty($categoryCustomFieldName)) {
        $returnFields[$categoryCustomFieldName] = 1;
      }
    } catch (CiviCRM_API3_Exception $e) {}

    return $returnFields;
  }

  /**
   * This method returns the links that are given for each search row.
   * currently the links added for each row are
   *
   * - View
   * - Edit
   *
   * @param bool $isDeleted
   * @param null $key
   *
   * @return array
   */
  public static function &links($isDeleted = FALSE, $key = NULL) {
    $actionLinks = [];

    foreach (self::$_links as $key => $value) {
      $actionLinks['primaryActions'][$key] = $value;
    }

    return $actionLinks;
  }

  /**
   * Getter for array of the parameters required for creating pager.
   *
   * @param $action
   * @param array $params
   */
  public function getPagerParams($action, &$params) {
    $params['status'] = ts('Case') . ' %%StatusMessage%%';
    $params['csvString'] = NULL;
    $params['buttonTop'] = 'PagerTopButton';
    $params['buttonBottom'] = 'PagerBottomButton';
    $params['rowCount'] = CRM_Supportcase_Utils_Setting::getDefaultCountOfRows();
  }

  /**
   * Returns total number of rows for the query.
   *
   * @param
   *
   * @return int
   *   Total number of rows
   */
  public function getTotalCount($action) {
    //huck to apply our new tables
    $this->_query->_simpleFromClause = CRM_Contact_BAO_Query::fromClause($this->_query->_whereTables, NULL, NULL, $this->_query->_primaryLocation, $this->_query->_mode);

    return $this->_query->searchQuery(0, 0, NULL,
      TRUE, FALSE,
      FALSE, FALSE,
      FALSE,
      $this->_additionalClause
    );
  }

  /**
   * Returns all the rows in the given offset and rowCount.
   *
   * @param string $action
   *   The action being performed.
   * @param int $offset
   *   The row number to start from.
   * @param int $rowCount
   *   The number of rows to return.
   * @param string $sort
   *   The sql string that describes the sort order.
   * @param string $output
   *   What should the result set include (web/email/csv).
   *
   * @return array
   * @throws CRM_Core_Exception
   * @throws CiviCRM_API3_Exception
   */
  public function &getRows($action, $offset, $rowCount, $sort, $output = NULL) {
    $result = $this->_query->searchQuery($offset, $rowCount, $sort,
      FALSE, FALSE,
      FALSE, FALSE,
      FALSE,
      $this->_additionalClause
    );

    $supportCasePermissions = [
      'manage' => CRM_Core_Permission::check('access support cases'),
      'delete' => CRM_Core_Permission::check('delete in CiviCase'),
      'restore' => CRM_Core_Permission::check('delete in CiviCase'),
      'spam' => CRM_Core_Permission::check('access support cases'),
    ];

    $rows = [];
    $foundCaseIds = [];
    $caseStatus = CRM_Core_OptionGroup::values('case_status', FALSE, FALSE, FALSE, " AND v.name = 'Urgent' ");
    $categoryCustomFieldName = CRM_Core_BAO_CustomField::getCustomFieldID('category', CRM_Supportcase_Install_Entity_CustomGroup::CASE_DETAILS, true);
    $qfKey = CRM_Utils_Request::retrieve('qfKey', 'String');
    if (empty($qfKey)) {
      $qfKey = '';
    }

    while ($result->fetch()) {
      $row = [];
      $foundCaseIds[] = $result->case_id;

      // the columns we are interested in
      foreach (self::$_properties as $property) {
        if (isset($result->$property)) {
          $row[$property] = $result->$property;
        }
      }

      if ($result->case_deleted) {
        $row['case_status_id'] = empty($row['case_status_id']) ? "" : $row['case_status_id'] . '<br />' . ts('(deleted)');
      }

      $row['checkbox'] = CRM_Core_Form::CB_PREFIX . $result->case_id;
      $caseManagerContactIds = CRM_Supportcase_Utils_CaseManager::getCaseManagerContactIds($result->case_id);

      $row['contact_type'] = CRM_Contact_BAO_Contact_Utils::getImage($result->contact_sub_type ? $result->contact_sub_type : $result->contact_type);
      $row['case_manager_contact_ids'] = $caseManagerContactIds;
      $row['case_manager_contacts'] = $this->getContactsInfo($caseManagerContactIds);
      $row['class'] = (isset($result->case_status_id) && array_key_exists($result->case_status_id, $caseStatus)) ? "status-urgent" : "status-normal";
      $row['case_tags'] = $this->getCaseTags($result->case_id);
      $row['category'] = (!empty($categoryCustomFieldName)) ? $result->$categoryCustomFieldName: '';
      $row['is_case_deleted'] = $result->case_deleted == '1';
      $row['manage_case_link'] = CRM_Utils_System::url('civicrm/a/', NULL, TRUE, 'supportcase/manage-case/' . $result->case_id . '/' . $qfKey . '/');
      $row['report_spam_link'] = CRM_Utils_System::url('civicrm/supportcase/report-spam', ['id' => $result->case_id, 'context' => 'search', 'reset' => 1]);
      $row['delete_case_link'] = CRM_Utils_System::url('civicrm/contact/view/case', ['id' => $result->case_id, 'context' => 'search', 'reset' => 1, 'action' => 'delete',]);
      $row['restore_case_link'] = CRM_Utils_System::url('civicrm/contact/view/case', ['id' => $result->case_id, 'context' => 'search', 'reset' => 1, 'action' => 'renew',]);
      $row['action_permissions'] = $supportCasePermissions;

      //default locking values:
      $row['is_case_locked'] = FALSE;
      $row['is_locked_by_self'] = FALSE;
      $row['lock_message'] = '';

      //it shows at 'most recent communication' column
      $mostRecentCommunicationData = CRM_Supportcase_Utils_Case::getRecentCommunication($result->case_id);
      $row['case_recent_activity_id'] = $mostRecentCommunicationData['activity_id'];
      $row['case_recent_activity_date'] = $mostRecentCommunicationData['activity_date_time'];
      $row['case_recent_activity_details'] = CRM_Supportcase_Utils_Activity::getEmailBody($mostRecentCommunicationData['activity_details'])['text'];
      $row['case_recent_activity_type_label'] = $mostRecentCommunicationData['activity_type_label'];

      $rows[$result->case_id] = $row;
    }

    //sets case locking info
    try {
      $lockedCases = civicrm_api3('CaseLock', 'get_locked_cases', [
        'case_ids' => $foundCaseIds,
      ]);
    } catch (CiviCRM_API3_Exception $e) {}

    if (!empty($lockedCases['values'])) {
      foreach ($lockedCases['values'] as $lockedCase) {
        $rows[$lockedCase['case_id']]['is_case_locked'] = $lockedCase['is_case_locked'];
        $rows[$lockedCase['case_id']]['is_locked_by_self'] = $lockedCase['is_locked_by_self'];
        $rows[$lockedCase['case_id']]['lock_message'] = $lockedCase['lock_message'];
      }
    }

    return $rows;
  }

  /**
   * Gets contacts info by ids
   *
   * @param $contactIds
   *
   * @return array
   */
  private function getContactsInfo($contactIds) {
    try {
      $contacts = civicrm_api3('Contact', 'get', [
        'sequential' => 1,
        'return' => ["phone", "display_name", "sort_name", "contact_id"],
        'id' => ['IN' => $contactIds],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return [];
    }

    return !empty($contacts['values']) ? $contacts['values'] : [];
  }

  /**
   * Gets tags assigned to case id
   *
   * @param $caseId
   *
   * @return array
   */
  private function getCaseTags($caseId) {
    $caseTagIds = CRM_Core_BAO_EntityTag::getTag($caseId, 'civicrm_case');
    if (empty($caseTagIds)) {
     return [];
    }

    $caseTags = [];
    foreach ($caseTagIds as $tagId) {
      if (isset($this->_caseAllTags[$tagId])) {
        $caseTags[] = $this->_caseAllTags[$tagId];
      }
    }

    return $caseTags;
  }

  /**
   * @inheritDoc
   */
  public function getQill() {
    return $this->_query->qill();
  }

  /**
   * Returns the column headers as an array of tuples:
   * (name, sortName (key to the sort array))
   *
   * @param string $action
   *   The action being performed.
   * @param string $output
   *   What should the result set include (web/email/csv).
   *
   * @return array
   *   the column headers that need to be displayed
   */
  public function &getColumnHeaders($action = NULL, $output = NULL) {
    if (!isset(self::$_columnHeaders)) {
      self::$_columnHeaders = [
        [
          'name' => ts('ID'),
          'sort' => 'case_id',
          'direction' => CRM_Utils_Sort::DONTCARE,
        ],
        [
          'name' => '',
          'direction' => CRM_Utils_Sort::DONTCARE,
        ],
        [
          'name' => ts('Client'),
          'sort' => 'sort_name',
          'direction' => CRM_Utils_Sort::DONTCARE,
        ],
        [
          'name' => ts('Subject'),
          'direction' => CRM_Utils_Sort::DONTCARE,
        ],
        [
          'name' => ts('Most Recent Communication'),
          'sort' => 'most_recent_communication',
          'direction' => CRM_Utils_Sort::DESCENDING,
        ],
        [
          'name' => ts('Tags'),
          'direction' => CRM_Utils_Sort::DONTCARE,
        ],
        [
          'name' => ts('Status'),
          'sort' => 'case_status',
          'direction' => CRM_Utils_Sort::DONTCARE,
        ],
        ['name' => ts('Actions')],
      ];
    }
    return self::$_columnHeaders;
  }

  /**
   * @return mixed
   */
  public function alphabetQuery() {
    return $this->_query->alphabetQuery();
  }

  /**
   * @return string
   */
  public function &getQuery() {
    return $this->_query;
  }

  /**
   * Get the action links for this page.
   *
   * @return array
   */
  public static function actionLinks() {
    return self::$_actionLinks;
  }

}
