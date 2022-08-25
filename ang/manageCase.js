(function(angular, $, _) {
    var moduleName = "manageCase";
    var moduleDependencies = ["ngRoute", "angularFileUpload"];
    angular.module(moduleName, moduleDependencies);

    angular.module(moduleName).config([
        "$routeProvider",
        function($routeProvider) {
            $routeProvider.when("/supportcase/manage-case/:caseId?/:dashboardSearchQfKey?/:prefillEmailId?", {
                controller: "manageCaseCtrl",
                templateUrl: "~/manageCase/manageCase.html",
                resolve: {
                    caseId: function($route) {
                        return angular.isDefined($route.current.params.caseId) ? $route.current.params.caseId : false;
                    },
                    dashboardSearchQfKey: function($route) {
                        return angular.isDefined($route.current.params.dashboardSearchQfKey) ? $route.current.params.dashboardSearchQfKey : false;
                    },
                    prefillEmailId: function($route) {
                        return angular.isDefined($route.current.params.prefillEmailId) ? $route.current.params.prefillEmailId : false;
                    },
                    apiCalls: function($route, crmApi) {
                        var reqs = {};

                        reqs.caseInfoResponse = ['SupportcaseManageCase', 'get_case_info', {
                            "sequential": 1,
                            "case_id": angular.isDefined($route.current.params.caseId) ? $route.current.params.caseId : 0,
                            options: {limit: 0}
                        }];

                        return crmApi(reqs);
                    }
                }
            });
        }
    ]);

    angular.module(moduleName).controller("manageCaseCtrl", function($scope, crmApi, apiCalls, caseId, dashboardSearchQfKey, prefillEmailId, $interval) {
        $scope.ts = CRM.ts();
        $scope.caseInfo = {};
        $scope.isError = false;
        $scope.caseLockId = undefined;
        $scope.errorMessage = '';
        $scope.isCaseLocked = false;
        if (dashboardSearchQfKey) {
            $scope.backUrl = CRM.url('civicrm/supportcase', {'qfKey': dashboardSearchQfKey});
        } else {
            $scope.backUrl = CRM.url('civicrm/supportcase');
        }

        //to add ability to use styles only for this page
        setTimeout(function() {
            CRM.$('body').addClass('manage-case-page');
        }, 0);

        $scope.handleCaseInfoResponse = function() {
            if (apiCalls.caseInfoResponse.is_error == 1) {
                $scope.isError = true;
                $scope.errorMessage = apiCalls.caseInfoResponse.error_message;
            } else {
                $scope.caseInfo = apiCalls.caseInfoResponse.values;
                $scope.caseInfo['dashboardSearchQfKey'] = dashboardSearchQfKey;
                $scope.isCaseLocked = $scope.caseInfo['is_case_locked'] && !$scope.caseInfo['is_locked_by_self'];
            }
        };

        $scope.getMangeCaseUpdateLockTime = function() {
            if ($scope.caseInfo !== undefined && $scope.caseInfo['settings']['mange_case_update_lock_time'] !== undefined) {
                return $scope.caseInfo['settings']['mange_case_update_lock_time'] * 1000;
            } else {
                return 10000;
            }
        };

        $scope.initLockTimer = function() {
            if ($scope.lockTimer !== undefined) {
                return;
            }

            $scope.lockTimer = $interval(function() {
                $scope.renewCaseLock();
            }, $scope.getMangeCaseUpdateLockTime());
        };

        $scope.renewCaseLock = function() {
            if ($scope.isCaseLocked) {
                return;
            }

            CRM.api3('CaseLock', 'renew_case_lock', {
                "case_lock_id": $scope.caseLockId
            }).then(function(result) {
                if (result.is_error === 1) {
                    if (result.error_code === "case_lock_does_not_exist") {
                        $scope.isCaseLocked = true;
                        $scope.$apply();
                    } else {
                        console.error('renew_case_lock get error:');
                        console.error(result.error_message);
                    }
                }
            }, function(error) {});
        };

        $scope.lockCase = function() {
            if ($scope.isCaseLocked) {
                return;
            }

            CRM.api3('CaseLock', 'lock_case', {
                "case_id": $scope.caseInfo.id
            }).then(function(result) {
                if (result.is_error === 1) {
                    console.error('lock_case get error:');
                    console.error(result.error_message);
                } else {
                    $scope.caseLockId = result.values[0]['id'];
                }
            }, function(error) {});
        };

        $scope.formatDateAndTime = function(standardCiviDate, isShowTime) {
            if (standardCiviDate === undefined || typeof standardCiviDate !== 'string') {
                return '';
            }
            var time = '';
            if (isShowTime) {
                time = $scope.formatTime(standardCiviDate);
            }

            return CRM.utils.formatDate(standardCiviDate) + ' ' + time;
        };

        $scope.formatTime = function(standardCiviDate) {
            var date = new Date(standardCiviDate);
            var hours = date.getHours();
            var minutes = date.getMinutes();
            var fixedMinutes = minutes < 10 ? '0' + minutes : minutes;

            if (CRM.config.timeIs24Hr) {
                return  hours + ':' + fixedMinutes;
            } else {
                var amPm = hours >= 12 ? 'pm' : 'am';
                var amPmHours = hours % 12;
                amPmHours = (amPmHours === 0) ? amPmHours : 12;
                return amPmHours + ':' + fixedMinutes + ' ' + amPm;
            }
        };

        $scope.$on('$destroy', function() {
            $interval.cancel($scope.lockTimer);
            $scope.lockTimer = undefined;
        });

        $scope.handleCaseInfoResponse();
        if (!$scope.isError) {
            $scope.initLockTimer();
            $scope.lockCase();
        }
    });

    angular.module(moduleName).service('cookieService', function() {
        this.cookieLiveTime = 365 * 24 * 60 * 60 * 1000;

        this.setCookie = function (cookieName, cookieValue) {
            var date = new Date();
            date.setTime(date.getTime() + this.cookieLiveTime);
            var expires = 'expires=' + date.toUTCString();
            document.cookie = cookieName + '=' + cookieValue + ';' + expires + ';path=/';
        }

        this.getCookie = function (cookieName) {
            var name = cookieName + '=';
            var ca = document.cookie.split(';');

            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];

                while (c.charAt(0) == ' ') {
                    c = c.substring(1);
                }

                if (c.indexOf(name) == 0) {
                    return c.substring(name.length, c.length);
                }
            }

            return '';
        }
    });

    angular.module(moduleName).service('reloadService', function() {
        this.reloadEmailsCallback = function() {};

        this.reloadEmails = function() {
            this.reloadEmailsCallback();
        };
        this.setReloadEmailsCallback = function(callback) {
            this.reloadEmailsCallback = callback;
        };
    });

    angular.module(moduleName).directive("caseInfo", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/caseInfo.html",
            scope: {model: "="},
            controller: function($scope, $element) {
                $scope.ts = CRM.ts();
                $scope.formatDateAndTime = $scope.$parent.formatDateAndTime;

                $scope.showHelpInfo = function(title, helpId, fileLocation) {
                    CRM.help(title, {
                        id: helpId,
                        file: fileLocation
                    });
                    return false;
                };

                $scope.getEntityLabel = function(entities, entityId) {
                    for (var i = 0; i < entities.length; i++) {
                        if (entities[i]['value'] === entityId) {
                            return entities[i]['label']
                        }
                    }

                    return '';
                };

                $scope.getInputStyles = function() {
                    return {
                        'width' : '100%',
                        'max-width' : '300px',
                        'box-sizing' : 'border-box',
                        'height' : '28px'
                    };
                };

                $scope.showError = function(directiveElement, errorMessage) {
                    var caseInfoItem = $(directiveElement);
                    if (caseInfoItem.length === 0) {
                        return;
                    }

                    caseInfoItem.find('.ci__case-info-errors-wrap').empty().append('<div class="crm-error">' + errorMessage + '</div>');
                };

                $scope.editConfirm = function(apiFieldName, apiFieldValue, directiveElement, successCallback) {
                    var apiParams = {"case_id": $scope.model['id']};
                    apiParams[apiFieldName] = apiFieldValue;

                    CRM.api3('SupportcaseManageCase', 'update_case_info', apiParams).then(function(result) {
                        if (result.is_error === 1) {
                            $scope.showError(directiveElement, result.error_message);
                        } else {
                            successCallback(result);
                        }
                    }, function(error) {});
                };
            }
        };
    });

    angular.module(moduleName).directive("caseSubject", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/caseInfo/caseSubject.html",
            scope: {model: "="},
            controller: function($scope, $element) {
                $scope.isEditMode = false;
                $scope.toggleMode = function() {
                    $($element).find('.ci__case-info-errors-wrap').empty();
                    $scope.isEditMode = !$scope.isEditMode;

                    if ($scope.isEditMode) {
                        $scope.setFieldFromModel();
                    }
                };
                $scope.setFieldFromModel = function() {$scope.subject = $scope.model['subject'];};
                $scope.editConfirm = function() {
                    $scope.$parent.editConfirm('subject', $scope.subject, $element, function(result) {
                        $scope.model['subject'] = $scope.subject;
                        $scope.toggleMode();
                        $scope.$apply();
                        CRM.status(ts('Subject updated.'));
                    });
                };

                $scope.setFieldFromModel();
            }
        };
    });

    angular.module(moduleName).directive("caseStatus", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/caseInfo/caseStatus.html",
            scope: {model: "="},
            controller: function($scope, $element) {
                $scope.isEditMode = false;
                $scope.toggleMode = function() {
                    $($element).find('.ci__case-info-errors-wrap').empty();
                    $scope.isEditMode = !$scope.isEditMode;

                    if ($scope.isEditMode) {
                        $scope.setFieldFromModel();
                        setTimeout(function() {
                            $($element).find(".ci__case-info-edit-mode select.spc--single-select").val($scope.statusId).trigger('change');
                        }, 0);
                    }
                };
                $scope.setFieldFromModel = function() {
                    $scope.statusId = $scope.model['status_id'];}
                ;
                $scope.updateInputValue = function() {
                    $scope.setFieldFromModel();
                    setTimeout(function() {
                        $($element).find(".ci__case-info-edit-mode select").val($scope.statusId).trigger('change');
                    }, 0);
                };
                $scope.getEntityLabel = $scope.$parent.getEntityLabel;
                $scope.editConfirm = function() {
                    $scope.$parent.editConfirm('status_id', $scope.statusId, $element, function(result) {
                        $scope.model['status_id'] = $scope.statusId;
                        $scope.toggleMode();
                        $scope.$apply();
                        CRM.status(ts('Status updated.'));
                    });
                };
                $scope.initSelect2 = function() {
                    setTimeout(function() {$($element).find(".ci__case-info-edit-mode select").css($scope.$parent.getInputStyles()).select2();}, 0);
                };

                $scope.setFieldFromModel();
                $scope.initSelect2();
            }
        };
    });

    angular.module(moduleName).directive("caseComment", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/caseInfo/caseComment.html",
            scope: {model: "="},
            controller: function($scope, $element) {
                $scope.comments = [];
                $scope.newCommentBody = '';
                $scope.isSimpleView = true;
                $scope.isShowCreateCommentWindow = false;
                $scope.formatDateAndTime = $scope.$parent.formatDateAndTime;

                this.$onInit = function() {
                    $scope.getComments();
                };

                $scope.toggleCreateComment = function() {
                    $scope.isShowCreateCommentWindow = !$scope.isShowCreateCommentWindow;
                    $scope.newCommentBody = '';
                    $scope.handleViewMode();
                };

                $scope.setMode = function(comment, mode) {
                    comment['mode'] = mode;
                    if (mode === 'edit') {
                        comment['newCommentBody'] = comment['details_text'];
                    }
                };

                $scope.handleViewMode = function() {
                    var isNeedToActivateSimpleMode = true;

                    if ($scope.comments.length > 0) {
                        isNeedToActivateSimpleMode = false;
                    }

                    if ($scope.isShowCreateCommentWindow) {
                        isNeedToActivateSimpleMode = false;
                    }

                    if (isNeedToActivateSimpleMode) {
                        $scope.isSimpleView = true;
                        CRM.$($element).addClass('ci--no-border').removeClass('ci--seven-items-width');
                    } else {
                        $scope.isSimpleView = false;
                        CRM.$($element).addClass('ci--seven-items-width').removeClass('ci--no-border');
                    }
                };

                $scope.createComment = function() {
                    CRM.api3('SupportcaseComment', 'create', {
                        "case_id" : $scope.model,
                        "comment" : $scope.newCommentBody
                    }).then(function(result) {
                        if (result.is_error === 1) {
                            console.error('"SupportcaseComment->create" get error:');
                            console.error(result.error_message);
                        } else {
                            $scope.getComments();
                            $scope.toggleCreateComment();
                            $scope.$apply();
                        }
                    }, function(error) {});
                };

                $scope.getComments = function() {
                    CRM.api3('SupportcaseComment', 'get', {
                        "sequential": 1,
                        "case_id": $scope.model,
                    }).then(function(result) {
                        if (result.is_error === 1) {
                            console.error('"SupportcaseComment->get" get error:');
                            console.error(result.error_message);
                        } else {
                            var comments = [];
                            for (var i = 0; i < result.values.length; i++) {
                                var comment = Object.assign({}, result.values[i]);
                                comment['mode'] = 'view';
                                comments.push(comment);
                            }

                            $scope.comments = comments;
                            $scope.handleViewMode();
                            $scope.$apply();
                        }
                    }, function(error) {});
                };

                $scope.deleteComment = function(activityId) {
                    CRM.api3('SupportcaseComment', 'delete', {
                        "sequential": 1,
                        "case_id": $scope.model,
                        "activity_id": activityId,
                    }).then(function(result) {
                        $scope.getComments();
                        $scope.$apply();
                    }, function(error) {});
                };

                $scope.updateComment = function(comment) {
                    CRM.api3('SupportcaseComment', 'update', {
                        "sequential": 1,
                        "case_id": $scope.model,
                        "activity_id": comment.id,
                        "comment": comment['newCommentBody'],
                    }).then(function(result) {
                        $scope.getComments();
                        $scope.$apply();
                    }, function(error) {});
                };
            }
        };
    });

    angular.module(moduleName).directive("caseClients", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/caseInfo/caseClients.html",
            scope: {model: "="},
            controller: function($scope, $window, $element) {
                $scope.showHelpInfo = $scope.$parent.showHelpInfo;
                $scope.isEditMode = false;
                $scope.toggleMode = function() {
                    $($element).find('.ci__case-info-errors-wrap').empty();
                    $scope.isEditMode = !$scope.isEditMode;

                    if ($scope.isEditMode) {
                        $scope.setFieldFromModel();
                        $($element).find("input.sc__select-contact-input").trigger('updateAllOptionsFromServer');
                    }
                };
                $scope.setFieldFromModel = function() {$scope.clientId = $scope.model['client_ids'][0];};
                $scope.editConfirm = function() {
                    $scope.$parent.editConfirm('new_case_client_id', $scope.clientId, $element, function(result) {
                        CRM.alert('Client was successfully updated. Refreshing page.', 'Change case client', 'success');
                        if ($scope.model['id'] != result.values.case.id) {
                          $window.location.href = "#/supportcase/manage-case/" + result.values.case.id;
                        } else {
                          window.location.reload();
                        }
                        $scope.toggleMode();
                    });
                };

                $scope.setFieldFromModel();
            }
        };
    });

    angular.module(moduleName).directive("caseManagers", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/caseInfo/caseManagers.html",
            scope: {model: "="},
            controller: function($scope, $window, $element) {
                $scope.isEditMode = false;
                $scope.toggleMode = function() {
                    $($element).find('.ci__case-info-errors-wrap').empty();
                    $scope.isEditMode = !$scope.isEditMode;

                    if ($scope.isEditMode) {
                        $scope.setFieldFromModel();
                        $($element).find("input.sc__select-contact-input").trigger('updateAllOptionsFromServer');
                    }
                };
                $scope.setFieldFromModel = function() {$scope.managerIds = $scope.model['managers_ids'];};
                $scope.editConfirm = function() {
                    if (typeof $scope.managerIds === 'string') {
                        $scope.managerIds = ($scope.managerIds.length === 0) ? [] : $scope.managerIds.split(",");
                    }
                    $scope.$parent.editConfirm('new_case_manager_ids', $scope.managerIds, $element, function(result) {
                        $scope.model['managers_ids'] = $scope.managerIds;
                        $scope.toggleMode();
                        $scope.$apply();
                        CRM.status(ts('Managers are updated.'));
                    });
                };

                $scope.setFieldFromModel();
            }
        };
    });

    angular.module(moduleName).directive("caseRelatedContacts", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/caseInfo/caseRelatedContacts.html",
            scope: {model: "="},
            controller: function($scope, $window, $element) {
                $scope.isEditMode = false;
                $scope.toggleMode = function() {
                    $($element).find('.ci__case-info-errors-wrap').empty();
                    $scope.isEditMode = !$scope.isEditMode;

                    if ($scope.isEditMode) {
                        $scope.setFieldFromModel();
                        $($element).find("input.sc__select-contact-input").trigger('updateAllOptionsFromServer');
                    }
                };
                $scope.setFieldFromModel = function() {$scope.relatedContactIds = $scope.model['related_contact_data']['related_contact_ids'];};
                $scope.editConfirm = function() {
                    if (typeof $scope.relatedContactIds === 'string') {
                        $scope.relatedContactIds = ($scope.relatedContactIds.length === 0) ? [] : $scope.relatedContactIds.split(",");
                    }
                    $scope.$parent.editConfirm('new_related_contact_ids', $scope.relatedContactIds, $element, function(result) {
                        $scope.model['related_contact_data']['related_contact_ids'] = $scope.relatedContactIds;
                        $scope.toggleMode();
                        $scope.$apply();
                        CRM.status(ts('Related contacts are updated.'));
                    });
                };

                $scope.setFieldFromModel();
            }
        };
    });

    angular.module(moduleName).directive("contactInfo", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/contactInfo.html",
            scope: {
                model: "=",
                isSearchDuplicates: "<isSearchDuplicates",
            },
            controller: function($scope, $element) {
                $scope.contact = [];
                $scope.isLoading = true;
                $scope.isOpenDescription = false;
                $scope.generateStyles = function(tagColor) {
                    var style = "";
                    if (tagColor !== null && tagColor !== undefined) {
                        style = "background-color: " + tagColor + " ; color: " + CRM.utils.colorContrast(tagColor) + ";";
                    }

                    return style;
                };
                this.$onInit = function() {
                    CRM.api3('SupportcaseManageCase', 'get_contact_info', {
                        "sequential": 1,
                        "contact_id": $scope.model,
                        "is_search_duplicates": $scope.isSearchDuplicates ? 1 : 0,
                    }).then(function(result) {
                        if (result.is_error === 1) {
                            console.error('"get_contact_info" action get error:');
                            console.error(result.error_message);
                        } else {
                            $scope.contact = result['values'][0];
                            $scope.isLoading = false;
                            $scope.$apply();
                        }
                    }, function(error) {});
                };
                $scope.toggleDescription = function() {
                    $scope.isOpenDescription = !$scope.isOpenDescription;
                    $($element).find(".contact-info__accordion-body").slideToggle('fast');
                };
            }
        };
    });

    angular.module(moduleName).directive("caseStartDate", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/caseInfo/caseStartDate.html",
            scope: {model: "="},
            controller: function($scope, $element) {
                $scope.formatDateAndTime = $scope.$parent.formatDateAndTime;
                $scope.isEditMode = false;
                $scope.toggleMode = function() {
                    $($element).find('.ci__case-info-errors-wrap').empty();
                    $scope.isEditMode = !$scope.isEditMode;

                    if ($scope.isEditMode) {
                        $scope.setFieldFromModel();
                    }
                };
                $scope.setFieldFromModel = function() {$scope.startDate = $scope.model['start_date'];};
                $scope.updateInputValue = function() {
                    $scope.setFieldFromModel();
                    setTimeout(function() {
                        $($element).find(".ci__case-info-edit-mode input").val($scope.startDate).trigger('change');
                    }, 0);
                };
                $scope.editConfirm = function() {
                    $scope.$parent.editConfirm('start_date', $scope.startDate, $element, function(result) {
                        $scope.model['start_date'] = $scope.startDate;
                        $scope.toggleMode();
                        $scope.$apply();
                        CRM.status(ts('Start date updated.'));
                    });
                };

                $scope.setFieldFromModel();
            }
        };
    });

    angular.module(moduleName).directive("caseCategory", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/caseInfo/caseCategory.html",
            scope: {model: "="},
            controller: function($scope, $element, reloadService) {
                $scope.isEditMode = false;
                $scope.toggleMode = function() {
                    $($element).find('.ci__case-info-errors-wrap').empty();
                    $scope.isEditMode = !$scope.isEditMode;

                    if ($scope.isEditMode) {
                        $scope.setFieldFromModel();
                        setTimeout(function() {
                            $($element).find(".ci__case-info-edit-mode select.spc--single-select").val($scope.categoryId).trigger('change');
                        }, 0);
                    }
                };
                $scope.getEntityLabel = $scope.$parent.getEntityLabel;
                $scope.setFieldFromModel = function() {$scope.categoryId = $scope.model['category_id'];};
                $scope.editConfirm = function() {
                    $scope.$parent.editConfirm('category_id', $scope.categoryId, $element, function(result) {
                        $scope.model['category_id'] = $scope.categoryId;
                        $scope.toggleMode();
                        $scope.$apply();
                        CRM.status(ts('Category updated.'));
                        reloadService.reloadEmails();
                    });
                };
                $scope.initSelect2 = function() {
                    setTimeout(function() {$($element).find(".ci__case-info-edit-mode select").css($scope.$parent.getInputStyles()).select2();}, 0);
                };

                $scope.setFieldFromModel();
                $scope.initSelect2();
            }
        };
    });

    angular.module(moduleName).directive("caseTags", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/caseInfo/caseTags.html",
            scope: {model: "="},
            controller: function($scope, $element) {
                $scope.isEditMode = false;
                $scope.toggleMode = function() {
                    $($element).find('.ci__case-info-errors-wrap').empty();
                    $scope.isEditMode = !$scope.isEditMode;

                    if ($scope.isEditMode) {
                        $scope.setFieldFromModel();
                        setTimeout(function() {
                            $($element).find(".ci__case-info-edit-mode select").val($scope.caseTags).trigger('change');
                        }, 0);
                    }
                };
                $scope.setFieldFromModel = function() {$scope.caseTags = $scope.model['tags_ids'];};
                $scope.generateStyles = function(tagColor) {
                    var style = "";
                    if (tagColor !== null && tagColor !== undefined) {
                        style = "background-color: " + tagColor + " ; color: " + CRM.utils.colorContrast(tagColor) + ";";
                    }

                    return style;
                };
                $scope.updateInputValue = function() {
                    $scope.setFieldFromModel();
                    setTimeout(function() {
                        $($element).find(".ci__case-info-edit-mode select").val($scope.caseTags).trigger('change');
                    }, 0);
                };
                $scope.editConfirm = function() {
                    var tagsIds = ($scope.caseTags === undefined) ? [] : $scope.caseTags;
                    $scope.$parent.editConfirm('tags_ids', tagsIds, $element, function(result) {
                        $scope.model['tags_ids'] = $scope.caseTags;
                        $scope.toggleMode();
                        CRM.status(ts('Tags updated.'));
                        $scope.$apply();
                    });
                };
                $scope.initSelect2 = function() {
                    var inputStyles =  $scope.$parent.getInputStyles();
                    inputStyles['height'] = 'auto';
                    setTimeout(function() {$($element).find(".ci__case-info-edit-mode select").css(inputStyles).select2();}, 0);
                };

                $scope.setFieldFromModel();
                $scope.initSelect2();
            }
        };
    });

    angular.module(moduleName).directive("emailCommunication", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/communication/email.html",
            scope: {model: "="},
            controller: function($scope, $element, $sce, reloadService) {
                $scope.formatDateAndTime = $scope.$parent.formatDateAndTime;
                $scope.emailActivities = [];
                $scope.draftEmailActivities = [];
                $scope.isLoading = false;
                $scope.availableModes = {
                    'view' : 'view',
                    'reply' : 'reply',
                    'reply_all' : 'reply_all',
                    'forward' : 'forward',
                    'origin' : 'origin',
                };
                $scope.isEmailSending = false;
                $scope.ts = CRM.ts();

                $scope.getEmails = function(callback) {
                    $scope.isLoading = true;

                    CRM.api3('SupportcaseManageCase', 'get_email_activities', {
                        "sequential": 1,
                        "case_id": $scope.model['case_id'],
                    }).then(function(result) {
                        if (result.is_error === 1) {
                            console.error('Activity get error:');
                            console.error(result.error_message);
                            CRM.status('Error via getting emails', 'error');
                        } else {
                            var emailActivities = [];
                            var draftEmailActivities = [];
                            var totalCount = 0;

                            for (var i = 0; i < result.values[0].emails.length; i++) {
                                emailActivities[i] = Object.assign({}, result.values[0].emails[i]);
                                emailActivities[i]['emailBodyResolved'] = $sce.trustAsHtml(result.values[0].emails[i]['email_body']);
                            }

                            for (var j = 0; j < result.values[0].drafts.length; j++) {
                                draftEmailActivities[j] = Object.assign({}, result.values[0].drafts[j]);
                                draftEmailActivities[j]['emailBodyResolved'] = $sce.trustAsHtml(result.values[0].drafts[j]['email_body']);
                            }

                            if (emailActivities.length > 0) {
                                totalCount += emailActivities.length;
                            }

                            if (draftEmailActivities.length > 0) {
                                totalCount += draftEmailActivities.length;
                            }

                            if (totalCount > 0) {
                                $scope.model.openMainAccordion();
                                $scope.model.count = totalCount;
                            }

                            $scope.emailActivities = emailActivities;
                            $scope.draftEmailActivities = draftEmailActivities;
                            $scope.isLoading = false;

                            $scope.$apply();
                            if (callback !== undefined) {
                                callback();
                            }
                        }
                    }, function(error) {
                        console.error('Activity get server error:');
                        CRM.status('Server error via getting emails', 'error');
                    });
                }

                reloadService.setReloadEmailsCallback($scope.getEmails);

                this.$onInit = function() {
                    $scope.getEmails();
                };

                $scope.switchToMode = function(mode, activityId) {
                    $scope.getActivity(activityId).current_mode = mode;
                };

                $scope.getActivity = function(activityId) {
                    return  $scope.emailActivities.filter(function(el, key) {return el.id === activityId;})[0];
                };

                $scope.cancel = function(activityId) {
                    $scope.cleanErrors(activityId);
                    $scope.switchToMode($scope.availableModes.view, activityId);
                };

                $scope.viewFullscreen = function(activityId) {
                    window.open(CRM.url('civicrm/supportcase/view-original', {'id' : activityId}), '_blank').focus();
                };

                $scope.getActivityElement = function(activityId) {
                    return CRM.$($element).find('.com__email-activity[data-activity-id="' + activityId + '"]');
                };

                $scope.highlightActivity = function(activityId) {
                    $scope.getActivityElement(activityId).find('.com__email-item-accordion-head').effect("highlight", {}, 6000);
                };

                $scope.scrollToActivity = function(activityId) {
                    $('html, body').animate({
                        scrollTop: parseInt($scope.getActivityElement(activityId).offset().top) - 100
                    }, 500);
                };

                $scope.showError = function(errorMessage, activityId, mode) {
                    $scope.cleanErrors(activityId, mode);
                    $scope.getActivityElement(activityId).find('.com__errors-wrap.com__errors-mode-' + mode).append('<div class="crm-error">' + errorMessage + '</div>');
                };

                $scope.cleanErrors = function(activityId) {
                    $scope.getActivityElement(activityId).find('.com__errors-wrap').empty();
                };

                $scope.toggleHeight = function(emailActivityId) {
                    CRM.$($element).find('.com__email-activity[data-activity-id="' + emailActivityId + '"]').toggleClass('com--full-height');
                };
            }
        };
    });

    angular.module(moduleName).directive("newEmail", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/communication/newEmail.html",
            scope: {model: "="},
            controller: function($scope, $element) {
                $scope.formatDateAndTime = $scope.$parent.formatDateAndTime;
                $scope.getEmails = $scope.$parent.getEmails;
                $scope.isShowSendEmailWindow = false;
                $scope.ts = CRM.ts();
                $scope.cancel = function () {
                    $scope.isShowSendEmailWindow = false;
                };

                $scope.toggleSendEmailWindow = function() {
                    $scope.isShowSendEmailWindow = !$scope.isShowSendEmailWindow;
                };
            }
        };
    });

    angular.module(moduleName).directive("smsCommunication", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/communication/sms.html",
            scope: {model: "="},
            controller: function($scope, $element) {
                $scope.smsActivities = [];

                this.$onInit = function() {
                    CRM.api3('SupportcaseManageCase', 'get_sms_activities', {
                        "sequential": 1,
                        "case_id": $scope.model['case_id'],
                    }).then(function(result) {
                        if (result.is_error === 1) {
                            console.error('Activity get error:');
                            console.error(result.error_message);
                        } else {
                            $scope.smsActivities = result.values;
                            if ($scope.smsActivities.length > 0) {
                                $scope.model.openMainAccordion();
                                $scope.model.count = $scope.smsActivities.length;
                            }
                            $scope.$apply();
                        }
                    }, function(error) {});
                };
            }
        };
    });

    angular.module(moduleName).directive("spcAttachmentUploader", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/communication/attachment/spcAttachmentUploader.html",
            scope: {
                model: "=",
                activityId: "<activityId",
            },
            controller: function($scope, $element, CrmAttachments) {
                if ($scope.activityId === undefined) {
                    console.error('Error loading attachments!');
                    return;
                }

                $scope.model = new CrmAttachments(function () {
                    return {entity_table: 'civicrm_activity', entity_id: $scope.activityId};
                });
                $scope.model.load();
            }
        };
    });

    angular.module(moduleName).directive("spcAttachment", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/communication/attachment/spcAttachment.html",
            scope: {
                model: "=",
                isAttachmentsCanBeSelected: "<isAttachmentsCanBeSelected",
            },
            controller: function($scope, $element) {
                this.$onInit = function() {
                    for (var i = 0; i < $scope['model']['length']; i++) {
                        $scope['model'][i]['isAdded'] = true;
                    }
                };

                $scope.toggleAdding = function(attachment) {
                    attachment.isAdded = !attachment.isAdded;
                };
            }
        };
    });

    angular.module(moduleName).directive("communication", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/communication.html",
            scope: {model: "="},
            controller: function($scope, $element) {
                $scope.formatDateAndTime = $scope.$parent.formatDateAndTime;
                $scope.ts = CRM.ts();

                $scope.openMainAccordion = function() {
                    var mainElement = $($element);
                    mainElement.find('.crm-accordion-wrapper').removeClass('collapsed');
                    mainElement.find('.crm-accordion-body').show();
                };
                $scope.sms = {
                    'count' : 0,
                    'case_id' : $scope.model['id'],
                    'openMainAccordion' : $scope.openMainAccordion,
                };
                $scope.email  = {
                    'count' : 0,
                    'draft_count' : 0,
                    'case_id' : $scope.model['id'],
                    'openMainAccordion' : $scope.openMainAccordion,
                    'new_email_prefill_fields' : $scope.model['new_email_prefill_fields'],
                };
            }
        };
    });

    /*
        Example:
        <div spc-accordion class="spc__accordion spc--blue-header spc--header-with-arrows spc--collapsed">
            <div class="spc__accordion-header">Name</div>
            <div class="spc__accordion-body">content</div>
        </div>
     */
    angular.module(moduleName).directive("spcAccordion", function() {
        return {
            restrict: "A",
            controller: function($element) {
                this.$onInit = function() {
                    var collapsedClassName = 'spc--collapsed';
                    var accordion = CRM.$($element);
                    var accordionHeader = accordion.children('.spc__accordion-header');
                    var accordionBody = accordion.children('.spc__accordion-body');
                    if (accordionHeader['length'] !== 1 || accordionBody['length'] !== 1) {
                        return;
                    }

                    accordionHeader.click(function() {
                        accordionBody.slideToggle( "fast", function() {
                            accordion.toggleClass(collapsedClassName);
                        });
                    });
                };
            }
        };
    });

    angular.module(moduleName).directive("activities", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/activities.html",
            scope: {model: "="},
            controller: function($scope) {
                $scope.formatDateAndTime = $scope.$parent.formatDateAndTime;
                $scope.activities = [];
                $scope.ts = CRM.ts();
                this.$onInit = function() {
                    CRM.api3('SupportcaseManageCase', 'get_activities', {
                        "sequential": 1,
                        "case_id": $scope.model['id'],
                    }).then(function(result) {
                        if (result.is_error === 1) {
                            console.error('Activity get error:');
                            console.error(result.error_message);
                        } else {
                            $scope.activities = result.values;
                            $scope.$apply();
                        }
                    }, function(error) {});
                };
            }
        };
    });

    angular.module(moduleName).directive("recentCases", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/recentCases.html",
            scope: {model: "="},
            controller: function($scope) {
                $scope.formatDateAndTime = $scope.$parent.formatDateAndTime;
                $scope.ts = CRM.ts();
                $scope.recentCases = [];
                $scope.updateRecentCases = function() {
                    CRM.api3('SupportcaseManageCase', 'get_recent_cases', {
                        "client_id": $scope.model['recent_case_for_contact_id'],
                        "limit_per_page": 0,
                        "page_number": 1
                    }).then(function(result) {
                        if (result.is_error === 1) {
                            console.error('Recent cases get error:');
                            console.error(result.error_message);
                        } else {
                            $scope.recentCases = result.values;
                            $scope.$apply();
                        }
                    }, function(error) {});
                };

                $scope.updateRecentCases();
            }
        };
    });

    angular.module(moduleName).directive("managePanel", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/managePanel.html",
            scope: {model: "="},
            controller: function($scope, $window, $element) {
                if ($scope.model['dashboardSearchQfKey']) {
                    $scope.backUrl = CRM.url('civicrm/supportcase', {'qfKey': $scope.model['dashboardSearchQfKey']});
                } else {
                    $scope.backUrl = CRM.url('civicrm/supportcase');
                }

                $scope.showError = function(errorMessage) {
                    $($element).find('.mp__error-wrap').empty().append('<div class="crm-error">' + errorMessage + '</div>');
                };

                $scope.doAction = function(apiFieldName, apiFieldValue, successCallback) {
                    var apiParams = {"case_id": $scope.model['id']};
                    apiParams[apiFieldName] = apiFieldValue;

                    CRM.api3('SupportcaseManageCase', 'update_case_info', apiParams).then(function(result) {
                        if (result.is_error === 1) {
                            $scope.showError(result.error_message);
                        } else {
                            successCallback();
                        }
                    }, function(error) {});
                };

                $scope.isCaseHasStatus = function(statusName) {
                   return $scope.model['settings']['case_status_ids'][statusName] === $scope.model['status_id'];
                };

                $scope.isCaseDeleted = function() {
                   return $scope.model['is_deleted'] == '1';
                };

                $scope.resolveCase = function() {
                    $scope.doAction('status_id', $scope.model['settings']['case_status_ids']['resolve'], function () {
                        $scope.model['status_id'] = $scope.model['settings']['case_status_ids']['resolve'];
                        CRM.status('Case was resolved.');
                        if ($scope.model['dashboardSearchQfKey']) {
                            window.location.href = CRM.url('civicrm/supportcase', {'qfKey': $scope.model['dashboardSearchQfKey']});
                        } else {
                            window.location.href = CRM.url('civicrm/supportcase');
                        }
                    });
                };

                $scope.reportSpamCase = function() {
                    $scope.doAction('status_id', $scope.model['settings']['case_status_ids']['spam'],function () {
                        $scope.model['status_id'] = $scope.model['settings']['case_status_ids']['spam'];
                        $scope.$apply();
                        CRM.status('Case was marked as spam.');
                        if ($scope.model['dashboardSearchQfKey']) {
                          window.location.href = CRM.url('civicrm/supportcase', {'qfKey': $scope.model['dashboardSearchQfKey']});
                        } else {
                          window.location.href = CRM.url('civicrm/supportcase');
                        }
                    });
                };

                $scope.makeCaseUrgent = function() {
                    $scope.doAction('status_id', $scope.model['settings']['case_status_ids']['urgent'],function () {
                        $scope.model['status_id'] = $scope.model['settings']['case_status_ids']['urgent'];
                        $scope.$apply();
                        CRM.status('Case was made urgent.');
                    });
                };

                $scope.makeCaseOngoing = function() {
                    $scope.doAction('status_id', $scope.model['settings']['case_status_ids']['ongoing'],function () {
                        $scope.model['status_id'] = $scope.model['settings']['case_status_ids']['ongoing'];
                        $scope.$apply();
                        CRM.status('Case was made ongoing.');
                    });
                };

                $scope.moveToTrashCase = function() {
                    $scope.doAction('is_deleted', '1',function () {
                        $scope.model['is_deleted'] = 1;
                        $scope.$apply();
                        CRM.status('Case was deleted.');
                    });
                };

                $scope.restoreCase = function() {
                    $scope.doAction('is_deleted', '0',function () {
                        $scope.model['is_deleted'] = 0;
                        $scope.$apply();
                        CRM.status('Case was restored.');
                    });
                };
            }
        };
    });

    angular.module(moduleName).directive("quickActions", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/quickActions.html",
            scope: {model: "="},
            controller: function($scope, $window, $element) {
                $scope.model['activeAction'] = false;
                $scope.runQuickAction = function(actionName) {
                    $scope.model['activeAction'] = actionName;
                };
                $scope.closeAction = function() {
                    $scope.model['activeAction'] = false;
                };
                $scope.$on('$destroy', function() {
                    $scope.closeAction();
                });
                $scope.showPreloader = function() {
                    $($element).find('.qa__preloader').addClass('active');
                };
                $scope.hidePreloader = function() {
                    $($element).find('.qa__preloader').removeClass('active');
                };
            }
        };
    });

    angular.module(moduleName).directive("exampleAction", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/actions/exampleAction.html",
            scope: {model: "="},
            controller: function($scope, $window, $element) {
                $scope.closeAction = $scope.$parent.closeAction;
                $scope.showPreloader = $scope.$parent.showPreloader;
                $scope.hidePreloader = $scope.$parent.hidePreloader;
            }
        };
    });

    angular.module(moduleName).directive("doNotSms", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/actions/doNotSms.html",
            scope: {model: "="},
            controller: function($scope, $window, $element) {
                $scope.closeAction = $scope.$parent.closeAction;
                $scope.showPreloader = $scope.$parent.showPreloader;
                $scope.hidePreloader = $scope.$parent.hidePreloader;
                $scope.info = {
                    'stepName' : 'confirmNumberStep',
                    'phoneNumber' : $scope.model['phone_number_for_do_not_sms_action'],
                    'contacts' : [],
                };

                $scope.runStep = function(nextStepName) {
                    if (nextStepName === 'selectContactsStep' && $scope.info.stepName === 'confirmNumberStep') {
                        $scope.findContactsByNumber();
                    } else if (nextStepName === 'showSuccessMessageStep') {
                        $scope.applyNoSmsToContacts();
                    }

                    $scope.info.stepName = nextStepName;
                };

                $scope.findContactsByNumber = function() {
                    $scope.showPreloader();
                    CRM.api3('SupportcaseQuickAction', 'find_contacts_by_number', {
                        'phone_number' : $scope.info.phoneNumber
                    }).then(function(result) {
                        if (result.is_error === 1) {
                            console.error('find_contacts_by_number get error:');
                            console.error(result.error_message);
                        } else {
                            $scope.info.contacts = result.values.map(function(contact) {
                                return {
                                   'id' : contact['id'],
                                   'display_name' : contact['display_name'],
                                   'link' : contact['link'],
                                   'is_do_not_sms' : contact['is_do_not_sms'],
                                   'is_selected' : true,
                                };
                            });
                            $scope.$apply();
                        }
                        $scope.hidePreloader();
                    }, function(error) {});
                };

                $scope.applyNoSmsToContacts = function() {
                    $scope.showPreloader();
                    var selectedContactIds = [];
                    for (var i = 0; i < $scope.info.contacts.length; i++) {
                        if ($scope.info.contacts[i]['is_selected'] === true) {
                            selectedContactIds.push($scope.info.contacts[i]['id']);
                        }
                    }

                    CRM.api3('SupportcaseQuickAction', 'apply_no_sms', {
                        'contact_ids' : selectedContactIds,
                        "case_id": $scope.model['id'],
                    }).then(function(result) {
                        if (result.is_error === 1) {
                            console.error('apply_no_sms get error:');
                            console.error(result.error_message);
                        }
                        $scope.hidePreloader();
                    }, function(error) {});
                };

                $scope.handleMainCheckbox = function($event) {
                    var isCheckedMainCheckbox = $($event.target).prop("checked");
                    for (var i = 0; i < $scope.info.contacts.length; i++) {
                        $scope.info.contacts[i]['is_selected'] = isCheckedMainCheckbox;
                    }
                };

                $scope.isSelectedMinimumOneContacts = function() {
                    for (var i = 0; i < $scope.info.contacts.length; i++) {
                        if ($scope.info.contacts[i]['is_selected'] === true) {
                            return true;
                        }
                    }

                    return false;
                };

                $scope.isSelectedAllContacts = function() {
                    for (var i = 0; i < $scope.info.contacts.length; i++) {
                        if ($scope.info.contacts[i]['is_selected'] === false) {
                            return false;
                        }
                    }

                    return true;
                };

                if ($scope.model['phone_number_for_do_not_sms_action'] != '') {
                  // skip confirmNumberStep if phone is preset
                  $scope.runStep('selectContactsStep');
                }
            }
        };
    });

    angular.module(moduleName).directive("manageEmailSubscriptions", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/actions/manageEmailSubscriptions.html",
            scope: {model: "="},
            controller: function($scope, $window, $element) {
                $scope.closeAction = $scope.$parent.closeAction;
                $scope.showPreloader = $scope.$parent.showPreloader;
                $scope.hidePreloader = $scope.$parent.hidePreloader;
                $scope.info = {
                    'stepName' : 'confirmEmailStep',
                    'email' : $scope.model['email_for_manage_email_subscriptions'],
                    'contacts' : [],
                    'availableGroups' : [],
                    'tableData' : [],
                    'tableHeaders' : [],
                };

                $scope.runStep = function(nextStepName) {
                    if (nextStepName === 'selectSubscriptionsStep' && $scope.info.stepName === 'confirmEmailStep') {
                        $scope.findContactsByEmail();
                    } else if (nextStepName === 'showSuccessMessageStep') {
                        $scope.applyGroups();
                    }

                    $scope.info.stepName = nextStepName;
                };

                $scope.unCheckAllGroupsToContact = function(contactId) {
                    var contactIndex = $scope.findContactIndex(contactId);
                    if (contactIndex === false) {
                        return;
                    }

                    for (var i = 0; i < $scope.info.availableGroups.length; i++) {
                        var groupKey = $scope.info.availableGroups[i]['name'];
                        $scope.info.tableData[contactIndex][groupKey] = false;
                    }
                };

                $scope.findContactIndex = function(contactId) {
                    for (var i = 0; i < $scope.info.tableData.length; i++) {
                        if ($scope.info.tableData[i]['contact_id'] == contactId) {
                            return i;
                        }
                    }

                    return false;
                };

                $scope.unCheckAllGroups = function() {
                    for (var i = 0; i < $scope.info.tableData.length; i++) {
                        for (var j = 0; j < $scope.info.availableGroups.length; j++) {
                            var groupKey = $scope.info.availableGroups[j]['name'];
                            $scope.info.tableData[i][groupKey] = false;
                        }
                    }
                };

                $scope.findContactsByEmail = function() {
                    $scope.showPreloader();
                    CRM.api3('SupportcaseQuickAction', 'find_contacts_by_email', {
                        'email' : $scope.info.email
                    }).then(function(result) {
                        if (result.is_error === 1) {
                            console.error('find_contacts_by_email get error:');
                            console.error(result.error_message);
                        } else {
                            $scope.info.tableHeaders = result.values['table_headers'];
                            $scope.info.tableData = result.values['table_data'];
                            $scope.info.availableGroups = result.values['available_groups'];
                            $scope.$apply();
                        }
                        $scope.hidePreloader();
                    }, function(error) {});
                };

                $scope.prepareOptOutData = function() {
                    var data = [];
                    for (var i = 0; i < $scope.info.tableData.length; i++) {
                        data.push({
                            "contact_id" : $scope.info.tableData[i]['contact_id'],
                            "is_opt_out" : $scope.info.tableData[i]['contact_is_opt_out'],
                        });
                    }

                    return data;
                };

                $scope.prepareGroupsData = function() {
                    var data = [];
                    for (var i = 0; i < $scope.info.tableData.length; i++) {
                        for (var j = 0; j < $scope.info.availableGroups.length; j++) {
                            var groupKey = $scope.info.availableGroups[j]['name'];
                            data.push({
                                "contact_id" : $scope.info.tableData[i]['contact_id'],
                                "group_id" : $scope.info.availableGroups[j]['id'],
                                "is_contact_in_group" : $scope.info.tableData[i][groupKey],
                            });
                        }
                    }

                    return data;
                };

                $scope.applyGroups = function() {
                    $scope.showPreloader();

                    CRM.api3('SupportcaseQuickAction', 'apply_groups', {
                        'groups_data' : $scope.prepareGroupsData(),
                        'opt_out_data' : $scope.prepareOptOutData(),
                        "case_id": $scope.model['id'],
                    }).then(function(result) {
                        if (result.is_error === 1) {
                            console.error('apply_groups get error:');
                            console.error(result.error_message);
                        }
                        $scope.hidePreloader();
                    }, function(error) {});
                };

              if ($scope.model['email_for_manage_email_subscriptions'] != '') {
                $scope.runStep('selectSubscriptionsStep');
              }
            }
        };
    });

    angular.module(moduleName).directive("showMoreText", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/showMoreText.html",
            scope: {
                fullText: "<fullText",
                maxTextLength: "<maxTextLength",
            },
            controller: function($scope) {
                if ($scope.fullText.length > $scope.maxTextLength) {
                    $scope.isShowLess = true;
                    $scope.isNeedToShowButtons = true;
                    $scope.shortText = $scope.fullText.substring(0, $scope.maxTextLength);
                    $scope.shortText += ' ...';
                } else {
                    $scope.isShowLess = false;
                    $scope.isNeedToShowButtons = false;
                }

                $scope.toggleMode = function() {
                    $scope.isShowLess = !$scope.isShowLess;
                };
            }
        };
    });

    angular.module(moduleName).directive("spcEmailEditor", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/communication/spcEmailEditor.html",
            scope: {
                supportCaseCategoryId: "=",
                tokenContactId: "=",
                emailBody: "=",
            },
            controller: function($scope, $element) {
                $scope.recentlyAddedTemplateClass = 'spc__recently-added-template';
                $scope.cursorClass = 'spc__editor-cursor';

                $scope.getCkeditorInstance = function() {
                    var item = $scope.getTextareaElement();
                    var name = $(item).attr("name"),
                        id = $(item).attr("id");
                    if (name && window.CKEDITOR && CKEDITOR.instances[name]) {
                        return CKEDITOR.instances[name];
                    }
                    if (id && window.CKEDITOR && CKEDITOR.instances[id]) {
                        return CKEDITOR.instances[id];
                    }

                    return undefined;
                }

                $scope.insertTemplateToEditor = function(template) {
                    var editor = $scope.getCkeditorInstance();
                    var selection = editor.getSelection();
                    editor.insertHtml(template, 'unfiltered_html');
                    var cursorElements = editor.editable().find('.' + $scope.cursorClass).toArray();
                    if (cursorElements.length > 0) {
                      var range = editor.createRange();
                      range.moveToElementEditStart(cursorElements[0]);
                      range.select().scrollIntoView();
                    }
                    // remove all cursor classes
                    cursorElements.forEach(function(element) {
                      element.removeClass($scope.cursorClass)
                    });
                }

                $scope.focusToCkeditor = function() {
                    function focusEditor() {
                      var editor = $scope.getCkeditorInstance();
                      if (editor === undefined || editor.document === undefined) {
                        // CKEditor is still initialising
                        setTimeout(focusEditor, 500);
                        return;
                      }
                      var cursorElements = editor.document.find('.' + $scope.cursorClass).toArray();

                      if (cursorElements.length > 0) {
                        editor.focus();
                        var range = editor.createRange();
                        range.moveToElementEditStart(cursorElements[0]);
                        range.select().scrollIntoView();
                      } else {
                        // TODO: replace this to config.startupFocus = true; now config doesn't work
                        editor.focus();
                      }
                      // remove all cursor classes
                      cursorElements.forEach(function(element) {
                        element.removeClass($scope.cursorClass)
                      });
                    }
                    focusEditor();
                }

                $scope.getTextareaElement = function() {
                    return $($element).find('.see__editor');
                }

                $scope.initCkeditor = function() {
                    var textareaElement = $scope.getTextareaElement()
                    textareaElement.attr('name', $scope.getUniqueName());
                    CRM.wysiwyg.create(textareaElement);
                }

                $scope.getUniqueName = function() {
                    return 'email_body_scope_id_' + $scope['$id'];
                }

                $scope.initCkeditor();
                $scope.focusToCkeditor();
            }
        };
    });

    angular.module(moduleName).directive("selectMailutilsTemplate", function() {
        return {
            restrict: "E",
            template: '<input type="text" class="spc__input spc--single-select" />',
            scope: {
                supportCaseCategoryId: "<supportCaseCategoryId",
                tokenContactId: "<tokenContactId",
                insertTemplateToEditor: "<insertTemplateToEditor",
            },
            controller: function($scope, $element) {
                var onSelect = function (e) {
                    e.preventDefault();

                    CRM.api3('SupportcaseManageCase', 'get_rendered_template', {
                        "id": e.object.mailutils_template_id,
                        "token_contact_id": $scope.tokenContactId,
                    }).then(function(result) {
                        if (result.is_error === 1) {
                            CRM.status('Error via getting token value:' + result.error_message, 'error');
                            console.error('SupportcaseManageCase->get_rendered_template error:');
                            console.error(result.error_message);
                        } else {
                            $($element).select2('close').select2('val', '');
                            $scope.insertTemplateToEditor(result['values']['rendered_text']);
                        }
                    }, function(error) {
                        console.error('SupportcaseManageCase->get_rendered_template error:');
                        console.error(error);
                        CRM.status('Error via getting token value.', 'error');
                    });
                };

                var loadTemplates = function() {
                    CRM.api3('SupportcaseManageCase', 'get_prepared_mail_template_options', {
                        "support_case_category_id": $scope.supportCaseCategoryId,
                        "token_contact_id": $scope.tokenContactId,
                    }).then(function(result) {
                        if (result.is_error === 1) {
                            console.error('SupportcaseManageCase->get_prepared_mail_template_options error:');
                            console.error(result.error_message);
                        } else {
                            $($element).addClass('crm-action-menu fa-code').crmSelect2({
                                width: "12em",
                                dropdownAutoWidth: true,
                                data: result['values'],
                                placeholder: ts('Templates')
                            });
                            $($element).on('select2-selecting', function (e) {onSelect(e);});
                        }
                    }, function(error) {
                        console.error('SupportcaseManageCase->get_prepared_mail_template_options error:');
                        console.error(error);
                    });
                };

                loadTemplates();
            }
        };
    });

    angular.module(moduleName).directive("selectEmail", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/selectEmail.html",
            scope: {
                model: "=",
                maxWidth: "<maxWidth",
                isMultiple: "<isMultiple",
                isRequired: "<isRequired",
                caseId: "<caseId",
            },
            controller: function($scope, $element) {
                $scope.entityName = 'SupportcaseEmail';
                $scope.newItemPseudoId = '_new_item_';
                $scope.isAlreadyInitSelect = false;
                $scope.currentSearchEmail = '';

                $scope.isValidEmail = function(emailString) {
                    var patern = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

                    return !!emailString.match(patern);
                };

                $scope.addNewItemAndSelectIt = function(newItem) {
                    var element = $($element).find(".se__select-email-input");
                    var data = element.select2('data');

                    if ($scope.isMultiple) {
                        data.push(newItem);
                    } else {
                        data = newItem;
                    }

                    element.select2('data', data, true);
                };

                $scope.addToClientEmail = function() {
                    var select2Element = $($element).find(".se__select-email-input");
                    select2Element.select2('close');

                    CRM.api3('SupportcaseManageCase', 'add_email_to_client', {
                        "case_id": $scope.caseId,
                        "email": $scope.currentSearchEmail
                    }).then(function(result) {
                        if (result.is_error === 1) {
                            console.error('"SupportcaseManageCase->add_email_to_client" get error:');
                            console.error(result.error_message);
                            CRM.status('Error via adding email to client:' + result.error_message, 'error');
                        } else {
                            CRM.status(result.values.message);
                            var item = {
                                id: result.values.data.email_id,
                                label: result.values.data.label,
                                icon: result.values.data.icon,
                                label_class: result.values.data.label_class,
                                description: result.values.data.description,
                            };
                            $scope.addNewItemAndSelectIt(item);
                        }
                    }, function(error) {
                        console.error('"SupportcaseManageCase->add_email_to_client" get error:');
                        console.error(result.error_message);
                        CRM.status('Error via adding email to client:' + result.error_message, 'error');
                    });
                };

                this.$onInit = function() {
                    setTimeout(function() {
                        var input = $($element).find(".se__select-email-input");
                        input.css({
                            'width' : '100%',
                            'max-width' : $scope.maxWidth + 'px',
                            'box-sizing' : 'border-box',
                        })

                        input.select2({
                            'tokenSeparators': [' '],
                            'closeOnSelect': false,
                            "multiple" : $scope.isMultiple,
                            'placeholder': '- none -',
                            'placeholderOption' : 'first',
                            'allowClear' : 'true',
                            'minimumInputLength' : 1,
                            'formatResult' :  function (row) {
                                var html = '<div class="crm-select2-row">';

                                html += '<div><div class="crm-select2-row-label '+(row.label_class || '')+'">';
                                html += (row.icon ? '<i class="crm-i ' + row.icon + '" aria-hidden="true"></i>&nbsp;&nbsp;' : '');
                                html +=  _.escape(row.label);
                                html += '</div>';

                                html += '<div class="crm-select2-row-description">';
                                $.each(row.description || [], function(k, text) {
                                    html += '<p>' + _.escape(text) + '</p> ';
                                });
                                html += '</div></div></div>';

                                return html;
                            },
                            'formatSelection' :  function(row) {
                                var html = '<div>';
                                html += row.icon ? '<div class="crm-select2-icon"><div class="crm-i ' + row.icon + '"></div></div>' : '';
                                html +=  _.escape(row.label);
                                html += '</div>';

                                return html;
                            },
                            'escapeMarkup' : _.identity,
                            'createSearchChoicePosition' : 'bottom',
                            'createSearchChoice' : function(searchString, selectedValues) {
                                if (!_.findKey(selectedValues, {label: searchString}) && $scope.isValidEmail(searchString)) {
                                    $scope.currentSearchEmail = searchString;

                                    // hack to add new custom item to select2
                                    setTimeout(function() {
                                        $( ".select2-drop-active ul.select2-results" ).append( `
                                            <li class="se_add-email-to-client-select2-option select2-results-dept-0 select2-result select2-result-selectable" role="presentation">
                                                <div class="select2-result-label" role="option">
                                                    <div class="crm-select2-row">
                                                        <div>
                                                            <div class="crm-select2-row-label se__color-blue">
                                                                <i class="crm-i fa-plus-circle" aria-hidden="true"></i>
                                                                    <span>&nbsp;&nbsp;Add email "` + $scope.currentSearchEmail + `" to client</span>
                                                            </div>
                                                            <div class="crm-select2-row-description">
                                                                <p>Create new <strong>email</strong> and add it to client</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        ` );
                                        $('.se_add-email-to-client-select2-option').click(function () {
                                            $scope.addToClientEmail();
                                        });
                                    }, 300);

                                    return {
                                        'id' : $scope.newItemPseudoId,
                                        'term' : searchString,
                                        'label' : searchString + ' (' + ts('Add new contact') + ')',
                                        'description' : ['Create new contact with "' + searchString + '" email'],
                                        'icon' : 'fa-plus-circle',
                                        'label_class' : 'se__color-blue',
                                    };
                                } else {
                                    $scope.currentSearchEmail = '';
                                    $( ".se_add-email-to-client-select2-option" ).remove();
                                }
                            },
                            'ajax': {
                                'url': CRM.url('civicrm/ajax/rest'),
                                'dataType': 'json',
                                'type': 'GET',
                                'quietMillis' : 300,
                                'data' : function (searchString, pageNumber) {
                                    return {
                                        'entity' : $scope.entityName,
                                        'action' : 'getlist',
                                        'json' : JSON.stringify({
                                            'input' : searchString,
                                            'page_num' : pageNumber,
                                        })
                                    };
                                },
                                'results' : function(data) {
                                    return {more: data.more_results, results: data.values || []};
                                }
                            },
                            'initSelection' : function($select, callback) {
                                if ($scope.isAlreadyInitSelect) {
                                    return;
                                }

                                $scope.isAlreadyInitSelect = true;
                                var val = $select.val();

                                if (val === '') {
                                    return;
                                }

                                var emailIds = val.split(',');

                                if (emailIds.length > 0) {
                                    CRM.api3($scope.entityName, 'getlist', {id: emailIds.join(',')}).done(function(result) {
                                        if (result['is_error'] === 0 && result.values.length > 0) {
                                            callback($scope.isMultiple ? result.values : result.values[0]);
                                            $select.trigger('change');
                                        }
                                    });
                                }
                            }
                        });

                        input.on('select2-selecting', function(e) {
                            if (e.val === $scope.newItemPseudoId) {
                                var emailName = e.choice.term;
                                CRM.api3($scope.entityName, 'create_new_contact_email', {email: emailName}).done(function(result) {
                                    var val = input.select2('val');
                                    var data = input.select2('data');

                                    if (result['is_error'] === 0) {
                                        var item = {
                                            id: result.values.email_id,
                                            label: result.values.label,
                                            icon: result.values.icon,
                                            label_class: result.values.label_class,
                                            description: result.values.description,
                                        };

                                        if (val === $scope.newItemPseudoId) {
                                            input.select2('data', item, true);
                                        } else if ($.isArray(val) && $.inArray($scope.newItemPseudoId, val) > -1) {
                                            _.remove(data, {id: $scope.newItemPseudoId});
                                            data.push(item);
                                            input.select2('data', data, true);
                                        }
                                    } else {
                                        CRM.alert(
                                            ts('Error message: ') + result['error_message'],
                                            ts('Error. Cannot create new contact email.') ,
                                            'error',
                                            {expires: 5000}
                                        );

                                        if (val === $scope.newItemPseudoId) {
                                            input.select2('data', {}, true);
                                        } else if ($.isArray(val) && $.inArray($scope.newItemPseudoId, val) > -1) {
                                            _.remove(data, {id: $scope.newItemPseudoId});
                                            input.select2('data', data, true);
                                        }
                                    }
                                });
                            }
                        });
                    }, 0);
                };
            }
        };
    });

    angular.module(moduleName).directive("selectContact", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/selectContact.html",
            scope: {
                model: "=",
                maxWidth: "<maxWidth",
                isMultiple: "<isMultiple",
                isRequired: "<isRequired",
                searchApiParams: "<searchApiParams",
            },
            controller: function($scope, $element) {
                $scope.entityName = 'Contact';
                $scope.isAlreadyInitSelect = false;
                if ($scope['searchApiParams'] === undefined) {
                    $scope['searchApiParams'] = {};
                }

                $scope.initSelect2 = function() {
                    setTimeout(function() {
                        var input = $($element).find(".sc__select-contact-input");
                        input.css({
                            'width' : '100%',
                            'max-width' : $scope.maxWidth + 'px',
                            'box-sizing' : 'border-box',
                        });

                        input.select2({
                            'tokenSeparators': [' '],
                            'closeOnSelect': false,
                            "multiple" : $scope.isMultiple,
                            'placeholder': '- none -',
                            'placeholderOption' : 'first',
                            'allowClear' : 'true',
                            'minimumInputLength' : 1,
                            'formatResult' :  function (row) {
                                var html = '<div class="crm-select2-row">';

                                html += '<div><div class="crm-select2-row-label '+(row.label_class || '')+'">';
                                html += (row.icon_class ? '<i class="crm-i ' + row.icon_class + '-icon"" aria-hidden="true"></i>&nbsp;&nbsp;' : '');
                                html +=  _.escape(row.label);
                                html += '</div>';

                                html += '<div class="crm-select2-row-description">';
                                $.each(row.description || [], function(k, text) {
                                    html += '<p>' + _.escape(text) + '</p> ';
                                });
                                html += '</div></div></div>';

                                return html;
                            },
                            'formatSelection' :  function(row) {
                                var html = '<div>';
                                html += row.icon ? '<div class="crm-select2-icon"><div class="crm-i ' + row.icon + '"></div></div>' : '';
                                html +=  _.escape(row.label);
                                html += '</div>';

                                return html;
                            },
                            'escapeMarkup' : _.identity,
                            'ajax': {
                                'url': CRM.url('civicrm/ajax/rest'),
                                'dataType': 'json',
                                'type': 'GET',
                                'quietMillis' : 300,
                                'data' : function (searchString, pageNumber) {
                                    return {
                                        'entity' : $scope.entityName,
                                        'action' : 'getlist',
                                        'json' : JSON.stringify({
                                            'input' : searchString,
                                            'page_num' : pageNumber,
                                            'params' : $scope['searchApiParams'],
                                        })
                                    };
                                },
                                'results' : function(data) {
                                    return {more: data.more_results, results: data.values || []};
                                }
                            },
                            'initSelection' : function($select, callback) {
                                if ($scope.isAlreadyInitSelect) {
                                    return;
                                }

                                $scope.isAlreadyInitSelect = true;
                                var val = $select.val();

                                if (val === '') {
                                    return;
                                }

                                var contactIds = val.split(',');

                                if (contactIds.length > 0) {
                                    CRM.api3($scope.entityName, 'getlist', {id: contactIds.join(',')}).done(function(result) {
                                        if (result['is_error'] === 0 && result.values.length > 0) {
                                            callback($scope.isMultiple ? result.values : result.values[0]);
                                            $select.trigger('change');
                                        }
                                    });
                                }
                            }
                        });

                        input.on('updateAllOptionsFromServer', function (e) {
                            setTimeout(function() {
                                if ($scope.model === undefined || $scope.model === '' || $scope.model['length'] === 0) {
                                    return;
                                }

                                CRM.api3('Contact', 'getlist', {
                                    "id": $scope.model
                                }).then(function(result) {
                                    if (result['is_error'] === 1) {
                                        console.error('Error via updating contact select. Api: Contact->getlist message:')
                                        console.error(result['error_message'])
                                    } else {
                                        var data = $scope.isMultiple ? result.values : result.values[0];
                                        input.select2('data', data, true);
                                        input.trigger('change');
                                    }
                                }, function(error) {
                                    console.error('Error via updating contact select. Api: Contact->getlist message:')
                                    console.error(error)
                                });
                            }, 150);
                        });
                    }, 0);
                };

                this.$onInit = function() {
                    $scope.initSelect2();
                };
            }
        };
    });

    angular.module(moduleName).directive("viewEmailOrigin", function() {
        return {
            restrict: "E",
            template: '<div class="com__iframe-origin">Loading...</div>',
            scope: {activityId: "<activityId"},
            controller: function($scope, $element) {
                this.$onInit = function() {
                    if ($scope.activityId === undefined) {
                        return;
                    }

                    var mainElement = CRM.$($element);
                    var iframeUrl = CRM.url('civicrm/supportcase/view-original', {'id' : $scope.activityId});
                    mainElement.empty();
                    mainElement.append('<iframe class="com__iframe-origin" sandbox="allow-popups allow-popups-to-escape-sandbox" src="' + iframeUrl + '"></iframe>');
                };
            }
        };
    });

    angular.module(moduleName).directive("emailEditor", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/communication/emailEditor.html",
            scope: {
                fromActivityId: "<fromActivityId",
                caseId: "<caseId",
                emailMode: "<emailMode",
                mailutilsMessageId: "<mailutilsMessageId",
                reloadEmailList: "<reloadEmailList",
                cancelCallback: "<cancelCallback",
            },
            controller: function($scope, $element, $timeout) {
                $scope.isShowEditorBlock = false;
                $scope.autoSaveTimer = null;
                $scope.isDisabledButtons = false;
                $scope.messages = [];
                $scope.draftReturnFields = [
                    'head_icon',
                    'subject',
                    'activity_id',
                    'date_time',
                    'case_id',
                    'mailutils_message_id',
                    'from_email_ids',
                    'to_email_ids',
                    'cc_email_ids',
                    'email_body',
                    'case_category_id',
                    'token_contact_id',
                    'email_auto_save_interval_time',
                    'email_body_raw',
                ];

                $scope.runCancelCallback = function () {
                    if (angular.isFunction($scope.cancelCallback)) {
                        $scope.cancelCallback();
                    }
                };

                $scope.addMessage = function (message) {
                    $scope.messages.push({
                        'text' : message,
                        'type': 'info'
                    });
                };

                $scope.addErrorMessage = function (message) {
                    $scope.messages.push({
                        'text' : message,
                        'type': 'error'
                    });
                };

                $scope.addUserErrorMessage = function (message) {
                    $scope.messages.push({
                        'text' : message,
                        'type': 'user_error'
                    });
                };

                $scope.clearMessages = function () {
                    $scope.messages = [];
                };

                $scope.clearInfoMessages = function () {
                    var messages = [];

                    for (var i = 0; i < $scope.messages.length; i++) {
                        if ($scope.messages[i]['type'] !== 'info') {
                            messages.push($scope.messages[i]);
                        }
                    }

                    $scope.messages = messages;
                };

                $scope.clearUserErrorMessages = function () {
                    var messages = [];

                    for (var i = 0; i < $scope.messages.length; i++) {
                        if ($scope.messages[i]['type'] !== 'user_error') {
                            messages.push($scope.messages[i]);
                        }
                    }

                    $scope.messages = messages;
                };

                $scope.disabledButtons = function () {
                    $scope.isDisabledButtons = true;
                };

                $scope.enableButtons = function () {
                    $scope.isDisabledButtons = false;
                };

                this.$onInit = function() {
                    if ($scope.emailMode === 'draft') {
                        $scope.loadDraft();
                    } else {
                        $scope.createDraft();
                    }
                };

                $scope.createDraft = function() {
                    $scope.clearInfoMessages();
                    $scope.addMessage('Email is loading ...');
                    $scope.disabledButtons();

                    CRM.api3('SupportcaseDraftEmail', 'create', {
                        "case_id": $scope.caseId,
                        "mode": $scope.emailMode,
                        "from_activity_id": $scope.fromActivityId,
                        "return": $scope.draftReturnFields
                    }).then(function(result) {
                        if (result.is_error === 1) {
                            $scope.handleApiError(result, 'SupportcaseDraftEmail', 'create');
                            return;
                        }
                        $scope.email = $scope.prepareEmail(result.values.data);
                        $scope.emailOnSever = Object.assign({}, $scope.email);
                        $scope.mailutilsMessageId = result.values.data.mailutils_message_id;
                        $scope.isShowEditorBlock = true;
                        $scope.clearInfoMessages();
                        $scope.enableButtons();
                        $scope.$apply();
                        $scope.startAutoSaving();
                    }, $scope.handleServerApiError);
                }

                $scope.loadDraft = function() {
                    $scope.clearInfoMessages();
                    $scope.addMessage('Email is loading ...');
                    $scope.disabledButtons();

                    CRM.api3('SupportcaseDraftEmail', 'get', {
                        "mailutils_message_id": $scope.mailutilsMessageId,
                        "return": $scope.draftReturnFields
                    }).then(function(result) {
                        if (result.is_error === 1) {
                            $scope.handleApiError(result, 'SupportcaseDraftEmail', 'get');
                            return;
                        }

                        $scope.email = $scope.prepareEmail(result.values);
                        $scope.emailOnSever = Object.assign({}, $scope.email);
                        $scope.mailutilsMessageId = result.values.mailutils_message_id;
                        $scope.isShowEditorBlock = true;
                        $scope.clearInfoMessages();
                        $scope.enableButtons();
                        $scope.$apply();
                        $scope.startAutoSaving();
                    }, $scope.handleServerApiError);
                }

                $scope.deleteDraft = function() {
                    $scope.clearInfoMessages();
                    $scope.disabledButtons();

                    if ($scope.emailMode === 'draft') {
                        $scope.addMessage('Email is deleting ...');
                    } else {
                        $scope.addMessage('Email is canceling ...');
                    }

                    CRM.api3('SupportcaseDraftEmail', 'delete_draft', {
                        "mailutils_message_id": $scope.mailutilsMessageId
                    }).then(function(result) {
                        if (result.is_error === 1) {
                            $scope.handleApiError(result, 'SupportcaseDraftEmail', 'delete_draft');
                            return;
                        }

                        $scope.isShowEditorBlock = false;
                        $scope.clearInfoMessages();
                        $scope.enableButtons();
                        $timeout.cancel($scope.autoSaveTimer);
                        $scope.$apply();
                        $scope.reloadEmailList();
                    }, $scope.handleServerApiError);
                }

                $scope.sendDraftApiCall = function() {
                    console.log('Sending email...');
                    $scope.clearInfoMessages();
                    $scope.addMessage('Email is sending ...');
                    $scope.disabledButtons();

                    CRM.api3('SupportcaseDraftEmail', 'send', {
                        "mailutils_message_id": $scope.mailutilsMessageId
                    }).then(function(result) {
                        if (result.is_error === 1) {
                            $scope.handleApiError(result, 'SupportcaseDraftEmail', 'send');
                            $scope.clearInfoMessages();
                            $scope.enableButtons();
                            $scope.enableButtons();
                            return;
                        }

                        $scope.isShowEditorBlock = false;
                        $scope.clearInfoMessages();
                        $scope.enableButtons();
                        $timeout.cancel($scope.autoSaveTimer);
                        $scope.$apply();
                        $scope.reloadEmailList();
                    }, $scope.handleServerApiError);
                }

                $scope.sendDraft = function() {
                    $scope.saveDraft(function () {
                        $scope.sendDraftApiCall();
                    });
                }

                $scope.saveAttachments = function(callback) {


                    //TODO: how to check if file description was changed?
                    if ($scope.email.additionalAttachments.uploader.queue.length > 0) {
                        console.log('Updating attachments: Uploading files ...');

                        $scope.email.additionalAttachments.save();

                        setTimeout(function() {
                            var interval = setInterval(function() {
                                if ($scope.email.additionalAttachments.uploader.isUploading === false) {
                                    clearInterval(interval);
                                    callback();
                                }
                            }, 50);
                        }, 50);
                    } else {
                        console.log('Updating attachments: No new attachments.');
                        callback();
                    }
                }

                $scope.saveDraftOnClick = function() {
                    $scope.saveDraft(function () {});
                }

                $scope.saveDraft = function(callback) {
                    if ($scope.$$destroyed === true) {
                        return;
                    }

                    $scope.saveAttachments(function () {
                        $scope.saveDraftCallApi(callback);
                    });

                }

                $scope.saveDraftCallApi = function(callback) {
                    var jsonUpdateParams = {
                        "mailutils_message_id": $scope.mailutilsMessageId,
                        "return": $scope.draftReturnFields
                    };
                    var isNeedToUpdate = false;

                    // when field is empty it has undefined value, but to correct compare need to convert to '' value
                    if ($scope.email.from === undefined) {$scope.email.from = '';}
                    if ($scope.email.to === undefined) {$scope.email.to = '';}
                    if ($scope.email.cc === undefined) {$scope.email.cc = '';}
                    if ($scope.email.subject === undefined) {$scope.email.subject = '';}
                    if ($scope.email.body === undefined) {$scope.email.body = '';}

                    if ($scope.emailOnSever.subject !== $scope.email.subject) {
                        jsonUpdateParams['subject'] = $scope.email.subject;
                        isNeedToUpdate = true;
                    }

                    if ($scope.emailOnSever.bodyRaw !== $scope.email.body) {
                        jsonUpdateParams['body'] = $scope.email.body;
                        isNeedToUpdate = true;
                    }

                    if ($scope.emailOnSever.from !== $scope.email.from) {
                        jsonUpdateParams['from_email_ids'] = $scope.email.from;
                        isNeedToUpdate = true;
                    }

                    if ($scope.emailOnSever.cc !== $scope.email.cc) {
                        jsonUpdateParams['cc_email_ids'] = $scope.email.cc;
                        isNeedToUpdate = true;
                    }

                    if ($scope.emailOnSever.to !== $scope.email.to) {
                        jsonUpdateParams['to_email_ids'] = $scope.email.to;
                        isNeedToUpdate = true;
                    }

                    if (!isNeedToUpdate) {
                        $scope.clearInfoMessages();
                        console.info('Updating draft data: Email is already saved. MailutilsMessageId: ' + $scope.mailutilsMessageId);
                        $scope.addMessage('No new changes. Email is already saved.');
                        $scope.startAutoSaving();
                        callback();
                        return;
                    }

                    $scope.clearInfoMessages();
                    $scope.addMessage('Email is saving ...');
                    $scope.disabledButtons();

                    //TODO: remove after test debug code
                    console.log('Start updating draft data,mailutilsMessageId: ' + $scope.mailutilsMessageId);
                    console.log('Server draft email:');
                    console.log($scope.emailOnSever);
                    console.log('Local draft email:');
                    console.log($scope.email);
                    console.log('Need to update draft params:');
                    console.log(jsonUpdateParams);

                    CRM.api3('SupportcaseDraftEmail', 'update_draft', jsonUpdateParams).then(function(result) {
                        if (result.is_error === 1) {
                            $scope.handleApiError(result, 'SupportcaseDraftEmail', 'update_draft');
                            return;
                        }

                        $scope.clearInfoMessages();
                        $scope.enableButtons();
                        $scope.emailOnSever = $scope.prepareEmail(result.values.data);
                        $scope.$apply();
                        CRM.status(ts('Email saved.'));
                        $scope.startAutoSaving();
                        callback();
                    }, $scope.handleServerApiError);
                }

                $scope.cancel = function() {
                    $scope.deleteDraft();
                    $scope.runCancelCallback();
                }

                $scope.startAutoSaving = function() {
                    if ($scope.$$destroyed === true) {
                        return;
                    }

                    $timeout.cancel($scope.autoSaveTimer);
                    $scope.autoSaveTimer = $timeout(function () {
                      $scope.saveDraft(function () {})
                    }, $scope.getEmailAutoSaveIntervalTime());
                }

                $scope.prepareEmail = function(email) {
                    return {
                        'from': email.from_email_ids,
                        'to': email.to_email_ids,
                        'cc': email.cc_email_ids,
                        'subject': email.subject,
                        'body' : email.email_body,
                        'bodyRaw' : email.email_body_raw,
                        'activityId' : email.activity_id,
                        'caseId' : $scope.caseId,
                        'caseCategoryId' : email.case_category_id,
                        'tokenContactId' : email.token_contact_id,
                        'emailAutoSaveIntervalTime' : email.email_auto_save_interval_time,
                        'additionalAttachments' : [],
                    };
                }

                $scope.handleServerApiError = function(error) {
                    $scope.clearUserErrorMessages();
                    $scope.addUserErrorMessage('Server error: ' + error);
                    console.error('Server error!');
                    console.error(error);
                }

                $scope.handleApiError = function(result, entity, action) {
                    $scope.clearUserErrorMessages();
                    $scope.addUserErrorMessage('Error: ' + result.error_message,);
                    console.error(entity + '->' + action + ' error:');
                    console.error(result.error_message);
                }

                $scope.getEmailAutoSaveIntervalTime = function() {
                    var interval = 10000;

                    if ($scope['email']['emailAutoSaveIntervalTime'] !== undefined) {
                        interval = 1000 * $scope['email']['emailAutoSaveIntervalTime'];
                    }

                    return interval;
                }
            }
        };
    });

})(angular, CRM.$, CRM._);
