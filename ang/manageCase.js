(function(angular, $, _) {
    var moduleName = "manageCase";
    var moduleDependencies = ["ngRoute"];
    angular.module(moduleName, moduleDependencies);

    angular.module(moduleName).config([
        "$routeProvider",
        function($routeProvider) {
            $routeProvider.when("/supportcase/manage-case/:caseId?", {
                controller: "manageCaseCtrl",
                templateUrl: "~/manageCase/manageCase.html",
                resolve: {
                    caseId: function($route) {
                        return angular.isDefined($route.current.params.caseId) ? $route.current.params.caseId : false;
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

    angular.module(moduleName).controller("manageCaseCtrl", function($scope, crmApi, apiCalls, caseId) {
        $scope.url = CRM.url;
        $scope.ts = CRM.ts();
        $scope.caseInfo = {};
        $scope.isError = false;
        $scope.errorMessage = '';
        $scope.handleCaseInfoResponse = function() {
            if (apiCalls.caseInfoResponse.is_error == 1) {
                $scope.isError = true;
                $scope.errorMessage = apiCalls.caseInfoResponse.error_message;
            } else {
                $scope.caseInfo = apiCalls.caseInfoResponse.values;
            }
        };

        $scope.handleCaseInfoResponse();
    });

    angular.module(moduleName).directive("caseInfo", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/caseInfo.html",
            scope: {
                model: "=",
            },
            bindToController: true,
            controllerAs: "ctrl",
            controller: function($scope) {
                $scope.ts = CRM.ts();

                $scope.getEntityLabel = function(entities, entityId) {
                    for (var i = 0; i < entities.length; i++) {
                        if (entities[i]['value'] === entityId) {
                            return entities[i]['label']
                        }
                    }

                    return '';
                };

                $scope.generateStyles = function(tagColor) {
                    var style = "";
                    if (tagColor !== null && tagColor !== undefined) {
                        style = "background-color: " + tagColor + " ; color: " + CRM.utils.colorContrast(tagColor) + ";";
                    }

                    return style;
                };

                $scope.toggleMode = function(field) {
                    var caseInfoItem = $('.ci__case-info-item.' + field);
                    if (caseInfoItem.length === 0) {
                        return;
                    }

                    caseInfoItem.find('.ci__case-info-errors-wrap').empty();
                    caseInfoItem.toggleClass('edit-mode');
                };

                $scope.showError = function(field, errorMessage) {
                    var caseInfoItem = $('.ci__case-info-item.' + field);
                    if (caseInfoItem.length === 0) {
                        return;
                    }

                     caseInfoItem.find('.ci__case-info-errors-wrap').empty().append('<div class="crm-error">' + errorMessage + '</div>');
                };

                $scope.editStartDateConfirm = function() {
                    $scope.toggleMode('startDateField');
                };

                $scope.editCategoryConfirm = function() {
                    CRM.api3('SupportcaseManageCase', 'update_case_info', {
                        "case_id": $scope.ctrl.model['id'],
                        "category_id": $scope.ctrl.model['category_id']
                    }).then(function(result) {
                        CRM.status(ts('Category updated.'));
                    }, function(error) {});
                    $scope.toggleMode('categoryField');
                };

                $scope.editTagsConfirm = function() {
                    $scope.toggleMode('tagsField');
                };
            }
        };
    });

    angular.module(moduleName).directive("caseSubject", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/caseInfo/caseSubject.html",
            scope: {
                model: "=",
            },
            bindToController: true,
            controllerAs: "ctrl",
            controller: function($scope) {
                $scope.toggleMode = $scope.$parent.toggleMode;
                $scope.showError = $scope.$parent.showError;
                $scope.setFieldFromModel = function() {
                    $scope.subject = $scope.ctrl.model['subject'];
                };
                $scope.editConfirm = function() {
                    CRM.api3('SupportcaseManageCase', 'update_case_info', {
                        "case_id": $scope.ctrl.model['id'],
                        "subject": $scope.subject,
                    }).then(function(result) {
                        if (result.is_error === 1) {
                            $scope.showError('subjectField', result.error_message);
                        } else {
                            $scope.ctrl.model['subject'] = $scope.subject;
                            $scope.$apply();
                            CRM.status(ts('Subject updated.'));
                            $scope.toggleMode('subjectField');
                        }
                    }, function(error) {});
                };

                $scope.setFieldFromModel();
            }
        };
    });

    angular.module(moduleName).directive("caseStatus", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/caseInfo/caseStatus.html",
            scope: {
                model: "=",
            },
            bindToController: true,
            controllerAs: "ctrl",
            controller: function($scope) {
                $scope.toggleMode = $scope.$parent.toggleMode;
                $scope.showError = $scope.$parent.showError;
                $scope.getEntityLabel = $scope.$parent.getEntityLabel;
                $scope.setFieldFromModel = function() {
                    $scope.statusId = $scope.ctrl.model['status_id'];
                };
                $scope.editConfirm = function() {
                    CRM.api3('SupportcaseManageCase', 'update_case_info', {
                        "case_id": $scope.ctrl.model['id'],
                        "status_id": $scope.statusId
                    }).then(function(result) {
                        if (result.is_error === 1) {
                            $scope.showError('statusField', result.error_message);
                        } else {
                            $scope.ctrl.model['status_id'] = $scope.statusId;
                            $scope.$apply();
                            CRM.status(ts('Status updated.'));
                            $scope.toggleMode('statusField');
                        }
                    }, function(error) {});
                };

                $scope.setFieldFromModel();
                var inputStyles =  {
                    'width' : '100%',
                    'max-width' : '300px',
                    'box-sizing' : 'border-box',
                    'height' : '28px'
                };
                setTimeout(function() {$(".ci__case-info-item.statusField select").css(inputStyles).select2();}, 0);
            }
        };
    });

    angular.module(moduleName).directive("caseClients", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/caseInfo/caseClients.html",
            scope: {
                model: "=",
            },
            bindToController: true,
            controllerAs: "ctrl",
            controller: function($scope, $window) {
                $scope.generateStyles = $scope.$parent.generateStyles;
                $scope.showError = $scope.$parent.showError;
                $scope.toggleMode = $scope.$parent.toggleMode;
                $scope.setFieldFromModel = function() {
                    $scope.clientId = $scope.ctrl.model['client_ids'][0];
                };
                $scope.editConfirm = function() {
                    CRM.api3('SupportcaseManageCase', 'update_case_info', {
                        "case_id": $scope.ctrl.model['id'],
                        "new_case_client_id": $scope.clientId
                    }).then(function(result) {
                        if (result.is_error === 1) {
                            $scope.showError('clientsField', result.error_message);
                        } else {
                            var message = '<ul>';
                            message += '<li>Case(id=' + $scope.ctrl.model['id'] + ') have been moved to the trash.</li>';
                            message += '<li>Created the same case(with activities) and new client.</li>';
                            message += '<li>You have been redireced to this new case.</li>';
                            message += '</ul>';
                            CRM.alert(message, 'Change case client', 'success');
                            $window.location.href = "#/supportcase/manage-case/" + result.values.case.id;
                        }
                    }, function(error) {});
                };
                $scope.toggleClientDescription = function(clientClassName) {
                    $('.ci__client.' + clientClassName).toggleClass('opened');
                };

                $scope.setFieldFromModel();
                var inputStyles =  {
                    'width' : '100%',
                    'max-width' : '300px',
                    'box-sizing' : 'border-box',
                    'height' : '28px'
                };
                setTimeout(function() {$(".ci__case-info-item.clientsField input").css(inputStyles).crmEntityRef();}, 0);
            }
        };
    });

    angular.module(moduleName).directive("caseStartDate", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/caseInfo/caseStartDate.html",
            scope: {
                model: "=",
            },
            bindToController: true,
            controllerAs: "ctrl",
            controller: function($scope) {
                $scope.toggleMode = $scope.$parent.toggleMode;
                $scope.showError = $scope.$parent.showError;
                $scope.setFieldFromModel = function() {
                    $scope.startDate = $scope.ctrl.model['start_date'];
                };
                $scope.editConfirm = function() {
                    CRM.api3('SupportcaseManageCase', 'update_case_info', {
                        "case_id": $scope.ctrl.model['id'],
                        "start_date": $scope.startDate
                    }).then(function(result) {
                        if (result.is_error === 1) {
                            $scope.showError('startDateField', result.error_message);
                        } else {
                            $scope.ctrl.model['start_date'] = $scope.startDate;
                            $scope.$apply();
                            CRM.status(ts('Start date updated.'));
                            $scope.toggleMode('startDateField');
                        }
                    }, function(error) {});
                };

                $scope.setFieldFromModel();
            }
        };
    });

    angular.module(moduleName).directive("caseCategory", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/caseInfo/caseCategory.html",
            scope: {
                model: "=",
            },
            bindToController: true,
            controllerAs: "ctrl",
            controller: function($scope) {
                $scope.toggleMode = $scope.$parent.toggleMode;
                $scope.getEntityLabel = $scope.$parent.getEntityLabel;
                $scope.showError = $scope.$parent.showError;
                $scope.setFieldFromModel = function() {
                    $scope.categoryId = $scope.ctrl.model['category_id'];
                };
                $scope.editConfirm = function() {
                    CRM.api3('SupportcaseManageCase', 'update_case_info', {
                        "case_id": $scope.ctrl.model['id'],
                        "category_id": $scope.categoryId
                    }).then(function(result) {
                        if (result.is_error === 1) {
                            $scope.showError('categoryField', result.error_message);
                        } else {
                            $scope.ctrl.model['category_id'] = $scope.categoryId;
                            $scope.$apply();
                            CRM.status(ts('Category updated.'));
                            $scope.toggleMode('categoryField');
                        }
                    }, function(error) {});
                };

                $scope.setFieldFromModel();
                var inputStyles =  {
                    'width' : '100%',
                    'max-width' : '300px',
                    'box-sizing' : 'border-box',
                    'height' : '28px'
                };
                setTimeout(function() {$(".ci__case-info-item.categoryField select").css(inputStyles).select2();}, 0);
            }
        };
    });

    angular.module(moduleName).directive("caseTags", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/caseInfo/caseTags.html",
            scope: {
                model: "=",
            },
            bindToController: true,
            controllerAs: "ctrl",
            controller: function($scope) {
                $scope.toggleMode = $scope.$parent.toggleMode;
                $scope.generateStyles = $scope.$parent.generateStyles;
                $scope.showError = $scope.$parent.showError;
                $scope.setFieldFromModel = function() {
                    $scope.caseTags = $scope.ctrl.model['tags_ids'];
                };
                $scope.editConfirm = function() {
                    CRM.api3('SupportcaseManageCase', 'update_case_info', {
                        "case_id": $scope.ctrl.model['id'],
                        "tags_ids": ($scope.caseTags === undefined) ? [] : $scope.caseTags
                    }).then(function(result) {
                        if (result.is_error === 1) {
                            $scope.showError('tagsField', result.error_message);
                        } else {
                            $scope.ctrl.model['tags_ids'] = $scope.caseTags;
                            CRM.status(ts('Tags updated.'));
                            $scope.toggleMode('tagsField');
                            $scope.$apply();
                        }
                    }, function(error) {});
                };
                $scope.setFieldFromModel();

                var inputStyles =  {
                    'width' : '100%',
                    'max-width' : '300px',
                    'box-sizing' : 'border-box',
                };
                setTimeout(function() {$(".ci__case-info-item.tagsField select").css(inputStyles).select2();}, 0);
            }
        };
    });

    angular.module(moduleName).directive("communication", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/communication.html",
            scope: {
                model: "=",
            },
            bindToController: true,
            controllerAs: "ctrl",
            controller: function($scope) {
                $scope.ts = CRM.ts();
            }
        };
    });

    angular.module(moduleName).directive("activities", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/activities.html",
            scope: {
                model: "=",
            },
            bindToController: true,
            controllerAs: "ctrl",
            controller: function($scope) {
                $scope.activities = [];
                $scope.ts = CRM.ts();
                this.$onInit = function() {
                    CRM.api3('SupportcaseManageCase', 'get_activities', {
                        "sequential": 1,
                        "case_id": $scope.ctrl.model['id'],
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
            scope: {
                model: "=",
            },
            bindToController: true,
            controllerAs: "ctrl",
            controller: function($scope) {
                $scope.ts = CRM.ts();
                $scope.recentCases = [];
                $scope.contactDisplayName = '';
                $scope.contactLink = '#';
                $scope.isContactExist = ($scope.ctrl.model['clients'][0] !== undefined);

                //TODO remove generating link from front
                if ($scope.isContactExist) {
                    $scope.contactDisplayName = $scope.ctrl.model['clients'][0]['display_name']
                    $scope.contactLink = CRM.url('civicrm/contact/view', {
                        reset: 1,
                        cid: $scope.ctrl.model['clients'][0]['contact_id']
                    });
                }

                $scope.updateRecentCases = function() {
                    if (!$scope.isContactExist) {
                        return;
                    }

                    CRM.api3('SupportcaseManageCase', 'get_recent_cases', {
                        "client_id": $scope.ctrl.model['clients'][0]['contact_id'],
                        "limit_per_page": 10,
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
            scope: {
                model: "=",
            },
            bindToController: true,
            controllerAs: "ctrl",
            controller: function($scope) {

            }
        };
    });

})(angular, CRM.$, CRM._);
