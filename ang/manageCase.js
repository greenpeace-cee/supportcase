(function(angular, $, _) {
    var moduleName = "manageCase";
    var moduleDependencies = ["ngRoute", "angularFileUpload"];
    angular.module(moduleName, moduleDependencies);

    angular.module(moduleName).config([
        "$routeProvider",
        function($routeProvider) {
            $routeProvider.when("/supportcase/manage-case/:caseId?/:dashboardSearchQfKey?", {
                controller: "manageCaseCtrl",
                templateUrl: "~/manageCase/manageCase.html",
                resolve: {
                    caseId: function($route) {
                        return angular.isDefined($route.current.params.caseId) ? $route.current.params.caseId : false;
                    },
                    dashboardSearchQfKey: function($route) {
                        return angular.isDefined($route.current.params.dashboardSearchQfKey) ? $route.current.params.dashboardSearchQfKey : false;
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

    angular.module(moduleName).controller("manageCaseCtrl", function($scope, crmApi, apiCalls, caseId, dashboardSearchQfKey, $interval) {
        $scope.ts = CRM.ts();
        $scope.caseInfo = {};
        $scope.isError = false;
        $scope.caseLockId = undefined;
        $scope.isCaseUnlocked = false;
        $scope.errorMessage = '';
        $scope.isCaseLocked = false;
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
            if ($scope.isCaseUnlocked) {
                return;
            }

            CRM.api3('CaseLock', 'renew_case_lock', {
                "case_lock_id": $scope.caseLockId
            }).then(function(result) {
                if (result.is_error === 1) {
                    if (result.error_code === "case_lock_does_not_exist") {
                        $scope.isCaseUnlocked = true;
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

                $scope.toggleMode = function(directiveElement) {
                    var caseInfoItem = $(directiveElement);
                    if (caseInfoItem.length === 0) {
                        return;
                    }

                    caseInfoItem.find('.ci__case-info-errors-wrap').empty();
                    caseInfoItem.find('.ci__case-info-item').toggleClass('edit-mode');
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
                            $scope.toggleMode(directiveElement);
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
                $scope.toggleMode = function() {$scope.$parent.toggleMode($element);};
                $scope.setFieldFromModel = function() {$scope.subject = $scope.model['subject'];};
                $scope.editConfirm = function() {
                    $scope.$parent.editConfirm('subject', $scope.subject, $element, function(result) {
                        $scope.model['subject'] = $scope.subject;
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
                $scope.toggleMode = function() {$scope.$parent.toggleMode($element);};
                $scope.setFieldFromModel = function() {$scope.statusId = $scope.model['status_id'];};
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
                        $scope.$apply();
                        CRM.status(ts('Status updated.'));
                    });
                };

                $scope.setFieldFromModel();
                setTimeout(function() {$($element).find(".ci__case-info-edit-mode select").css($scope.$parent.getInputStyles()).select2();}, 0);
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
                $scope.toggleMode = function() {$scope.$parent.toggleMode($element);};
                $scope.setFieldFromModel = function() {$scope.clientId = $scope.model['client_ids'][0];};
                $scope.editConfirm = function() {
                    $scope.$parent.editConfirm('new_case_client_id', $scope.clientId, $element, function(result) {
                        var message = '<ul>';
                        message += '<li>Case(id=' + $scope.model['id'] + ') have been moved to the trash.</li>';
                        message += '<li>Created the same case(with the same activities and tags) with new client.</li>';
                        message += '<li>You have been redirected to this new case(id=' + result.values.case.id + ').</li>';
                        message += '</ul>';
                        CRM.alert(message, 'Change case client', 'success');
                        $window.location.href = "#/supportcase/manage-case/" + result.values.case.id;
                    });
                };

                $scope.setFieldFromModel();
                setTimeout(function() {$($element).find(".ci__case-info-edit-mode input").css($scope.$parent.getInputStyles()).crmEntityRef();}, 0);
            }
        };
    });

    angular.module(moduleName).directive("caseManagers", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/caseInfo/caseManagers.html",
            scope: {model: "="},
            controller: function($scope, $window, $element) {
                $scope.toggleMode = function() {$scope.$parent.toggleMode($element);};
                $scope.setFieldFromModel = function() {$scope.managerIds = $scope.model['managers_ids'];};
                $scope.editConfirm = function() {
                    if (typeof $scope.managerIds === 'string') {
                        $scope.managerIds = ($scope.managerIds.length === 0) ? [] : $scope.managerIds.split(",");
                    }
                    $scope.$parent.editConfirm('new_case_manager_ids', $scope.managerIds, $element, function(result) {
                        $scope.model['managers_ids'] = $scope.managerIds;
                        $scope.$apply();
                        CRM.status(ts('Managers are updated.'));
                    });
                };

                $scope.setFieldFromModel();
                var inputStyles =  $scope.$parent.getInputStyles();
                inputStyles['height'] = 'auto';
                setTimeout(function() {$($element).find(".ci__case-info-edit-mode input").css(inputStyles).crmEntityRef();}, 0);
            }
        };
    });

    angular.module(moduleName).directive("contactInfo", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/contactInfo.html",
            scope: {model: "=", hidedupes: '='},
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
                $scope.setFieldFromModel = function() {$scope.startDate = $scope.model['start_date'];};
                $scope.toggleMode = function() {$scope.$parent.toggleMode($element);};
                $scope.updateInputValue = function() {
                    $scope.setFieldFromModel();
                    setTimeout(function() {
                        $($element).find(".ci__case-info-edit-mode input").val($scope.startDate).trigger('change');
                    }, 0);
                };
                $scope.editConfirm = function() {
                    $scope.$parent.editConfirm('start_date', $scope.startDate, $element, function(result) {
                        $scope.model['start_date'] = $scope.startDate;
                        $scope.$apply();
                        CRM.status(ts('Start date updated.'));
                    });
                };
            }
        };
    });

    angular.module(moduleName).directive("caseCategory", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/caseInfo/caseCategory.html",
            scope: {model: "="},
            controller: function($scope, $element, reloadService) {
                $scope.toggleMode = function() {$scope.$parent.toggleMode($element);};
                $scope.updateInputValue = function() {
                    $scope.setFieldFromModel();
                    setTimeout(function() {
                        $($element).find(".ci__case-info-edit-mode select").val($scope.categoryId).trigger('change');
                    }, 0);
                };
                $scope.getEntityLabel = $scope.$parent.getEntityLabel;
                $scope.setFieldFromModel = function() {$scope.categoryId = $scope.model['category_id'];};
                $scope.editConfirm = function() {
                    $scope.$parent.editConfirm('category_id', $scope.categoryId, $element, function(result) {
                        $scope.model['category_id'] = $scope.categoryId;
                        $scope.$apply();
                        CRM.status(ts('Category updated.'));
                        reloadService.reloadEmails();
                    });
                };

                $scope.setFieldFromModel();
                setTimeout(function() {$($element).find(".ci__case-info-edit-mode select").css($scope.$parent.getInputStyles()).select2();}, 0);
            }
        };
    });

    angular.module(moduleName).directive("caseTags", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/caseInfo/caseTags.html",
            scope: {model: "="},
            controller: function($scope, $element) {
                $scope.toggleMode = function() {$scope.$parent.toggleMode($element);};
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
                        CRM.status(ts('Tags updated.'));
                        $scope.$apply();
                    });
                };

                $scope.setFieldFromModel();
                var inputStyles =  $scope.$parent.getInputStyles();
                inputStyles['height'] = 'auto';
                setTimeout(function() {$($element).find(".ci__case-info-edit-mode select").css(inputStyles).select2();}, 0);
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
                            for (var i = 0; i < result.values.length; i++) {
                                emailActivities[i] = Object.assign({}, result.values[i]);
                                emailActivities[i]['modes'][$scope.availableModes.view]['emailBodyResolved'] = $sce.trustAsHtml(result.values[i]['modes'][$scope.availableModes.view]['email_body']);
                                emailActivities[i]['modes'][$scope.availableModes.forward]['additionalAttachments'] = {};
                                emailActivities[i]['modes'][$scope.availableModes.reply]['additionalAttachments'] = {};
                                emailActivities[i]['modes'][$scope.availableModes.reply_all]['additionalAttachments'] = {};
                            }

                            if (emailActivities.length > 0) {
                                $scope.model.openMainAccordion();
                                $scope.model.count = emailActivities.length;
                            }

                            $scope.emailActivities = emailActivities;
                            $scope.$apply();
                            $scope.handleEmailCollapsing();
                            if (callback !== undefined) {
                                callback();
                            }

                            console.log('$onInit');
                            console.log('$scope.emailActivities');
                            console.log($scope.emailActivities);
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
                    console.log('switchToMode');
                    console.log(activityId);
                    console.log(mode);
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

                $scope.removeForwardAttachment = function(fileId, activityForwardData) {
                    activityForwardData['attachments'] = activityForwardData['attachments'].filter(function (item) {
                        return item['file_id'] !== fileId
                    });
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

                $scope.prepareEmailSendData = function(activity) {
                    var emailData = activity['modes'][activity.current_mode];
                    var files = $scope.getFiles(activity['id'], activity.current_mode);
                    var data = {};

                    if (activity.current_mode === $scope.availableModes.forward) {
                        var forwardFileIds = [];
                        for (var j = 0; j < emailData['attachments'].length; j++) {
                            if (emailData['attachments'][j]['isAdded'] === true) {
                                forwardFileIds.push(emailData['attachments'][j]['file_id']);
                            }
                        }
                        data['forward_file_ids'] = forwardFileIds;
                    }

                    if (files['files'] !== undefined && files['files']['length'] > emailData['attachmentsLimit']) {
                        $scope.isEmailSending = false;
                        $scope.showError('To match attachments. Maximum is ' + emailData['attachmentsLimit'] + '.', activity['id'], activity.current_mode);
                        return;
                    }

                    data['case_id'] = $scope.model['case_id'];
                    data['subject'] = emailData['subject'];
                    data['mode'] = activity.current_mode;
                    data['email_activity_id'] = activity['id'];
                    data['body'] = emailData['email_body'];
                    data['to_email_id'] = emailData['emails']['to'];
                    data['from_email_id'] = emailData['emails']['from'];
                    data['cc_email_ids'] = emailData['emails']['cc'];
                    data['attachments'] = files['dataFiles'];

                    var formData = new FormData();
                    if (files['files'] !== undefined) {
                        for (var i = 0; i < files['files']['length']; i++) {
                            formData.append('attachments[]', files['files'][i]);
                        }
                    }

                    formData.append('entity', 'SupportcaseManageCase');
                    formData.append('action', 'send_email');
                    formData.append('json', JSON.stringify(data));

                    return formData;
                }

                $scope.send = function(activity) {
                    if ($scope.isEmailSending) {
                        console.error('Email is sending');
                        $scope.showError('Email is sending', activity['id'], activity.current_mode);
                        return;
                    }
                    $scope.isEmailSending = true;

                    if (!(activity.current_mode === $scope.availableModes.reply
                        || activity.current_mode === $scope.availableModes.forward
                        || activity.current_mode === $scope.availableModes.reply_all
                    )) {
                        console.error('Unknown mode');
                        $scope.isEmailSending = false;
                        return;
                    }

                    var data = $scope.prepareEmailSendData(activity);
                    console.log('data');
                    console.log(data);

                    $.ajax({
                        url : CRM.url('civicrm/ajax/rest'),
                        type : 'POST',
                        data : data,
                        processData: false,  // tell jQuery not to process the data
                        contentType: false,  // tell jQuery not to set contentType
                        success : function(response) {
                            if (typeof response === 'string') {
                                console.error('Error sending email:');
                                console.error('Error parsing response.');
                                $scope.showError('Error sending email: Error parsing response.', activity['id'], activity.current_mode);
                                $scope.isEmailSending = false;
                                return;
                            }

                            if (response['is_error'] === 0) {
                                CRM.status('Email is sent!');
                                var emailActivity = response['values']['activity_id'];
                                $scope.getEmails(function () {
                                    $scope.highlightActivity(emailActivity);
                                    $scope.scrollToActivity(emailActivity);
                                });
                            } else {
                                console.error('Error sending email:');
                                console.error(response['error_message']);
                                $scope.showError(response['error_message'], activity['id'], activity.current_mode);
                            }
                            $scope.isEmailSending = false;
                        },
                        error: function(data){
                            var message = 'Error sending email: Server error.'
                            console.error('Error sending email: Server error.');
                            console.error(data);
                            $scope.showError(message, activity['id'], activity.current_mode);
                            $scope.isEmailSending = false;
                        }
                    });
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

                $scope.handleEmailCollapsing = function() {
                    CRM.$($element).find('.com__email-activity-accordion:not(:first)').addClass('spc--collapsed');
                };

                $scope.getFiles = function(activityId, mode) {
                    var preparedData = {
                        'files' : [],
                        'dataFiles' : [],
                    }

                    var activity = $scope.getActivity(activityId);
                    if (activity === undefined) {
                        return preparedData;
                    }

                    if (activity['modes'][mode]['additionalAttachments'] === undefined) {
                        return preparedData;
                    }

                    if (activity['modes'][mode]['additionalAttachments']['uploader'] === undefined) {
                        return preparedData;
                    }

                    if (activity['modes'][mode]['additionalAttachments']['uploader']['queue'] === undefined) {
                        return preparedData;
                    }

                    var queueFiles = activity['modes'][mode]['additionalAttachments']['uploader']['queue'];

                    if (!(queueFiles['length'] > 0)) {
                        return preparedData;
                    }

                    for (var i = 0; queueFiles['length'] > i; i++ ) {
                        preparedData.files.push(queueFiles[i]['_file']);
                        preparedData.dataFiles.push({
                            'name' : queueFiles[i]['_file']['name'],
                            'description' : queueFiles[i]['crmData']['description']
                        });
                    }

                    return preparedData;
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
                $scope.reloadEmailList = $scope.$parent.getEmails;
                $scope.isShowSendEmailWindow = false;
                $scope.isEmailSending = false;
                $scope.ts = CRM.ts();
                $scope.emailData = {
                    'case_id': $scope.model['case_id'],
                    'subject': $scope.model['new_email_prefill_fields']['subject'],
                    'from_email_id': $scope.model['new_email_prefill_fields']['from_email_id'],
                    'to_email_id': $scope.model['new_email_prefill_fields']['to_email_id'],
                    'cc_email_ids': $scope.model['new_email_prefill_fields']['cc_email_ids'],
                    'body': $scope.model['new_email_prefill_fields']['email_body'],
                    'case_category_id': $scope.model['new_email_prefill_fields']['case_category_id'],
                    'token_contact_id': $scope.model['new_email_prefill_fields']['token_contact_id'],
                    'mode': 'new',
                    'attachments': [],
                };
                console.log('$scope.emailData');
                console.log($scope.emailData);

                $scope.refreshPrefillData = function() {
                    $scope.emailData['subject'] = $scope.model['new_email_prefill_fields']['subject'];
                    $scope.emailData['from_email_id'] = $scope.model['new_email_prefill_fields']['from_email_id'];
                    $scope.emailData['to_email_id'] = $scope.model['new_email_prefill_fields']['to_email_id'];
                    $scope.emailData['cc_email_ids'] = $scope.model['new_email_prefill_fields']['cc_email_ids'];
                    $scope.emailData['body'] = $scope.model['new_email_prefill_fields']['email_body'];
                    $scope.emailData['case_category_id'] = $scope.model['new_email_prefill_fields']['case_category_id'];
                };

                $scope.toggleSendEmailWindow = function() {
                    $scope.refreshPrefillData();
                    $scope.isShowSendEmailWindow = !$scope.isShowSendEmailWindow;
                };

                $scope.showError = function(errorMessage) {
                    $scope.cleanErrors();
                    CRM.$($element).find('.com__errors-wrap').append('<div class="crm-error">' + errorMessage + '</div>');
                };

                $scope.cleanErrors = function() {
                    CRM.$($element).find('.com__errors-wrap').empty();
                };

                $scope.send = function() {
                    if ($scope.isEmailSending) {
                        console.error('Email is sending');
                        $scope.showError('Email is sending');
                        return;
                    }
                    $scope.isEmailSending = true;

                    var formData = new FormData();
                    var files = $scope.getFiles();
                    var data = {};
                    data['case_id'] = $scope.emailData['case_id'];
                    data['subject'] = $scope.emailData['subject'];
                    data['body'] = $scope.emailData['body'];
                    data['mode'] = 'new';
                    data['to_email_id'] = $scope.emailData['to_email_id'];
                    data['from_email_id'] = $scope.emailData['from_email_id'];
                    data['cc_email_ids'] = $scope.emailData['cc_email_ids'];

                    if (files['files'] !== undefined) {
                        for (var i = 0; i < files['files']['length']; i++) {
                            formData.append('attachments[]', files['files'][i]);
                        }
                    }

                    formData.append('entity', 'SupportcaseManageCase');
                    formData.append('action', 'send_email');
                    formData.append('json', JSON.stringify(data));

                    $.ajax({
                        url : CRM.url('civicrm/ajax/rest'),
                        type : 'POST',
                        data : formData,
                        processData: false,  // tell jQuery not to process the data
                        contentType: false,  // tell jQuery not to set contentType
                        success : function(response) {
                            if (typeof response === 'string') {
                                console.error('Error sending email:');
                                console.error('Error parsing response.');
                                $scope.showError('Error sending email: Error parsing response.');
                                $scope.isEmailSending = false;
                                return;
                            }

                            if (response['is_error'] === 0) {
                                CRM.status('Email is sent!');
                                $scope.toggleSendEmailWindow();
                                $scope.reloadEmailList();
                                $scope.$apply();
                            } else {
                                console.error('Error sending email:');
                                console.error(response['error_message']);
                                $scope.showError(response['error_message']);
                            }
                            $scope.isEmailSending = false;
                        },
                        error: function(data){
                            var message = 'Error sending email: Server error.'
                            console.error('Error sending email: Server error.');
                            console.error(data);
                            $scope.showError(message);
                            $scope.isEmailSending = false;
                        }
                    });
                };

                $scope.getFiles = function() {
                    var preparedData = {
                        'files' : [],
                        'dataFiles' : [],
                    }

                    if ($scope.emailData['attachments'] === undefined) {
                        return preparedData;
                    }

                    if ($scope.emailData['attachments']['uploader'] === undefined) {
                        return preparedData;
                    }

                    if ($scope.emailData['attachments']['uploader']['queue'] === undefined) {
                        return preparedData;
                    }

                    var queueFiles = $scope.emailData['attachments']['uploader']['queue'];

                    if (!(queueFiles['length'] > 0)) {
                        return preparedData;
                    }

                    for (var i = 0; queueFiles['length'] > i; i++ ) {
                        preparedData.files.push(queueFiles[i]['_file']);
                        preparedData.dataFiles.push({
                            'name' : queueFiles[i]['_file']['name'],
                            'description' : queueFiles[i]['crmData']['description']
                        });
                    }

                    return preparedData;
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
            scope: {model: "="},
            controller: function($scope, $element, CrmAttachments) {
                $scope.model = new CrmAttachments({});
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
                    'case_id' : $scope.model['id'],
                    'openMainAccordion' : $scope.openMainAccordion,
                    'new_email_prefill_fields' : $scope.model['new_email_prefill_fields'],
                };
            }
        };
    });

    angular.module(moduleName).directive("mailutilsTemplate", function () {
        return {
            require: '^crmUiIdScope',
            scope: {
                onSelect: '@'
            },
            template: '<input type="text" class="crmMailingToken" />',
            link: function (scope, element, attrs, crmUiIdCtrl) {
                var loadTemplates = function() {
                    CRM.api3('SupportcaseManageCase', 'get_prepared_mail_template_options', {
                        "support_case_category_id": attrs['supportCaseCategoryId'],
                        "token_contact_id": attrs['tokenContactId'],
                    }).then(function(result) {
                        if (result.is_error === 1) {
                            console.error('SupportcaseManageCase->get_prepared_mail_template_options error:');
                            console.error(result.error_message);
                        } else {
                            $(element).addClass('crm-action-menu fa-code').crmSelect2({
                                width: "12em",
                                dropdownAutoWidth: true,
                                data: result['values'],
                                placeholder: ts('Templates')
                            });
                            $(element).on('select2-selecting', function (e) {
                                e.preventDefault();
                                $(element).select2('close').select2('val', '');
                                scope.$parent.$eval(attrs.onSelect, {
                                    token: {name: e.val}
                                });
                            });
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
                          console.log(result);
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

    angular.module(moduleName).directive('editorFocusOnLoad', function() {
        return {
            link: function(scope, element) {
                // TODO: to remove that hack we can make custom editor where can set 'focus on load'
                setTimeout(function() {
                    CRM.wysiwyg.focus(element);
                }, 1500);
            }
        };
    })

    angular.module(moduleName).directive("selectEmail", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/selectEmail.html",
            scope: {
                model: "=",
                maxWidth: "<maxWidth",
                isMultiple: "<isMultiple",
                isRequired: "<isRequired",
            },
            controller: function($scope, $element) {
                $scope.entityName = 'SupportcaseEmail';
                $scope.newItemPseudoId = '_new_item_';
                $scope.isAlreadyInitSelect = false;

                $scope.isValidEmail = function(emailString) {
                    var patern = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

                    return !!emailString.match(patern);
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
                                    return {
                                        'id' : $scope.newItemPseudoId,
                                        'term' : searchString,
                                        'label' : searchString + ' (' + ts('Add new email') + ')',
                                        'description' : ['Create new Contact with "' + searchString + '" email'],
                                        'icon' : 'fa-plus-circle',
                                        'label_class' : 'se__color-blue',
                                    };
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

})(angular, CRM.$, CRM._);
