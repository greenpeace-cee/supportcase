(function(angular, $, _) {
    var moduleName = "manageCase";
    var moduleDependencies = ["ngRoute"];
    angular.module(moduleName, moduleDependencies);

    angular.module(moduleName).config([
        "$routeProvider",
        function($routeProvider) {
            $routeProvider.when("/supportcase/manage-case/:caseId?/:viewType?", {
                controller: "manageCaseCtrl",
                templateUrl: "~/manageCase/manageCase.html",
                resolve: {
                    caseId: function($route) {
                        return angular.isDefined($route.current.params.caseId) ? $route.current.params.caseId : false;
                    },
                    isLoadedInIframe: function($route) {
                        return angular.isDefined($route.current.params.viewType) && $route.current.params.viewType == 'in-iframe';
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

    angular.module(moduleName).controller("manageCaseCtrl", function($scope, crmApi, apiCalls, caseId, isLoadedInIframe, $interval) {
        $scope.ts = CRM.ts();
        $scope.caseInfo = {};
        $scope.isError = false;
        $scope.caseLockId = undefined;
        $scope.isCaseUnlocked = false;
        $scope.errorMessage = '';
        $scope.isCaseLocked = false;
        $scope.handleCaseInfoResponse = function() {
            if (apiCalls.caseInfoResponse.is_error == 1) {
                $scope.isError = true;
                $scope.errorMessage = apiCalls.caseInfoResponse.error_message;
            } else {
                $scope.caseInfo = apiCalls.caseInfoResponse.values;
                $scope.isCaseLocked = $scope.caseInfo['is_case_locked'] && !$scope.caseInfo['is_locked_by_self'];
            }
        };

        //TODO: handle iframe in another way without js, try to fix issues when add param 'snippet = 1' to angular page
        $scope.handleIframe = function() {
            if (!isLoadedInIframe) {
                return;
            }

            $('#header').hide();
            $('body').css('background', 'white');
            $('#civicrm-menu-nav').hide();
            $('#page-title').hide();
            $('#content > .section .tabs').hide();
            $('#breadcrumb').hide();
            $('#civicrm-footer').hide();
            $('#footer-wrapper').hide();
            $('#access.footer').hide();
            $('.column.sidebar').hide();
            $('#sidebar-first').hide();
            $('#branding').hide();
            $('#toolbar').hide();
            $('#main').css('width', '100%');
            $('#main').css('margin', 0);
            $('#content').css('width', '100%');
            $('#content').css('margin', 0);
            var page = $('#page');
            page.css('margin-right', 0);
            page.css('margin-left', 0);
            page.css('padding', 0);

            var style = document.createElement('style');
            style.innerHTML = 'body.crm-menubar-visible.crm-menubar-over-cms-menu.crm-menubar-wrapped {padding-top: 0px !important;}';
            style.innerHTML += '@media (min-width: 768px) {';
            style.innerHTML += 'body.crm-menubar-visible.crm-menubar-over-cms-menu {padding-top: 0px !important;}';
            style.innerHTML += '}';
            document.head.appendChild(style);
            setTimeout(function () {
                var body = document.getElementsByTagName('body');
                if (body.length >= 1) {
                    body[0].style.paddingTop = '0';
                }
            }, 200);
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

        $scope.handleIframe();
        $scope.handleCaseInfoResponse();
        $scope.initLockTimer();
        $scope.lockCase();
    });

    angular.module(moduleName).directive("caseInfo", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/caseInfo.html",
            scope: {model: "="},
            controller: function($scope) {
                $scope.ts = CRM.ts();
                $scope.formatDateAndTime = $scope.$parent.formatDateAndTime;

                $scope.showHelpInfo =  function(title, helpId, fileLocation) {
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
            scope: {model: "="},
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
            controller: function($scope, $element) {
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

    angular.module(moduleName).directive("communication", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/communication.html",
            scope: {model: "="},
            controller: function($scope, $element) {
                $scope.formatDateAndTime = $scope.$parent.formatDateAndTime;
                $scope.smsActivities = [];
                $scope.emailActivities = [];
                $scope.recipients = '';
                $scope.isReplyId = null;
                $scope.replyMode = null;
                $scope.ts = CRM.ts();
                this.$onInit = function() {
                    CRM.api3('SupportcaseManageCase', 'get_sms_activities', {
                        "sequential": 1,
                        "case_id": $scope.model['id'],
                    }).then(function(result) {
                        if (result.is_error === 1) {
                            console.error('Activity get error:');
                            console.error(result.error_message);
                        } else {
                            $scope.smsActivities = result.values;
                            if (result.values["length"] > 0) {
                                var mainElement = $($element);
                                mainElement.find('.crm-accordion-wrapper').removeClass('collapsed');
                                mainElement.find('.crm-accordion-body').show();
                            }
                            $scope.$apply();
                        }
                    }, function(error) {});

                    $scope.getEmails();
                };

                $scope.initRecipientEntityRef = function() {
                    setTimeout(function() {
                        var input = $($element).find(".com__recipients-input");
                        input.crmEntityRef('destroy');
                        input.css({
                            'width' : '100%',
                            'max-width' : '300px',
                            'box-sizing' : 'border-box',
                        }).crmEntityRef();
                    }, 0);
                };

                $scope.reply = function(activity_id) {
                    $scope.initRecipientEntityRef();
                    $scope.currentReplyId = activity_id;
                    $scope.replyMode = 'reply';
                    $scope.emailSelect(CRM.$("input[name='to']"), null);
                };

                $scope.forward = function() {
                  $scope.currentReplyId = activity_id;
                  $scope.replyMode = 'forward';
                  $scope.emailSelect(CRM.$("input[name='to']"), null);
                };

                $scope.cancel = function() {
                  $scope.currentReplyId = null;
                  $scope.replyMode = null;
                };

                $scope.send = function(to_contact_id, to_email, from_contact_id, from_email, subject, body) {
                  CRM.api3('SupportcaseManageCase', 'send_email', {
                    "to_contact_id": to_contact_id,
                    "to_email": to_email,
                    "from_contact_id": from_contact_id,
                    "from_email": from_email,
                    "subject": subject,
                    "body": body,
                    "case_id": $scope.model['id'],
                  }).then(function(result) {
                    if (result.is_error === 1) {
                      console.error('Error sending email:');
                      console.error(result.error_message);
                    } else {
                      CRM.status('Email to ' + to_email + ' sent!');
                      $scope.getEmails();
                      $scope.isReplyMode = false;
                    }
                  }, function(error) {});
                };

                $scope.getEmails = function() {
                    CRM.api3('SupportcaseManageCase', 'get_email_activities', {
                      "sequential": 1,
                      "case_id": $scope.model['id'],
                    }).then(function(result) {
                      if (result.is_error === 1) {
                        console.error('Activity get error:');
                        console.error(result.error_message);
                      } else {
                        $scope.emailActivities = result.values;
                        if (result.values["length"] > 0) {
                          var mainElement = $($element);
                          mainElement.find('.crm-accordion-wrapper').removeClass('collapsed');
                          mainElement.find('.crm-accordion-body').show();
                        }
                        $scope.$apply();
                      }
                    }, function(error) {});
                }

                $scope.emailSelect = function(el, prepopulate) {
                  $(el).data('api-entity', 'contact').css({width: '40em', 'max-width': '90%'}).crmSelect2({
                    minimumInputLength: 1,
                    multiple: true,
                    ajax: {
                      url: '/civicrm/ajax/checkemail?id=1',
                      data: function(term) {
                        return {
                          name: term
                        };
                      },
                      results: function(response) {
                        return {
                          results: response
                        };
                      }
                    }
                  }).select2('data', prepopulate);
                }
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
                CRM.api3('SupportcaseManageCase', 'get_prepared_mail_template_options').then(function(result) {
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
                        $scope.$apply();
                        CRM.status('Case was resolved.');
                    });
                };

                $scope.reportSpamCase = function() {
                    $scope.doAction('status_id', $scope.model['settings']['case_status_ids']['spam'],function () {
                        $scope.model['status_id'] = $scope.model['settings']['case_status_ids']['spam'];
                        $scope.$apply();
                        CRM.status('Case was marked as spam.');
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

})(angular, CRM.$, CRM._);
