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
            scope: {model: "="},
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

                $scope.getInputStyles = function() {
                    return {
                        'width' : '100%',
                        'max-width' : '300px',
                        'box-sizing' : 'border-box',
                        'height' : '28px'
                    };
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

                $scope.editConfirm = function(apiFieldName, apiFieldValue, fieldSelector, successCallback) {
                    var apiParams = {"case_id": $scope.ctrl.model['id']};
                    apiParams[apiFieldName] = apiFieldValue;

                    CRM.api3('SupportcaseManageCase', 'update_case_info', apiParams).then(function(result) {
                        if (result.is_error === 1) {
                            $scope.showError(fieldSelector, result.error_message);
                        } else {
                            successCallback(result);
                            $scope.toggleMode(fieldSelector);
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
            bindToController: true,
            controllerAs: "ctrl",
            controller: function($scope) {
                $scope.toggleMode = $scope.$parent.toggleMode;
                $scope.setFieldFromModel = function() {$scope.subject = $scope.ctrl.model['subject'];};
                $scope.editConfirm = function() {
                    $scope.$parent.editConfirm('subject', $scope.subject, 'subjectField', function(result) {
                        $scope.ctrl.model['subject'] = $scope.subject;
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
            bindToController: true,
            controllerAs: "ctrl",
            controller: function($scope) {
                $scope.toggleMode = $scope.$parent.toggleMode;
                $scope.getEntityLabel = $scope.$parent.getEntityLabel;
                $scope.setFieldFromModel = function() {$scope.statusId = $scope.ctrl.model['status_id'];};
                $scope.editConfirm = function() {
                    $scope.$parent.editConfirm('status_id', $scope.statusId, 'statusField', function(result) {
                        $scope.ctrl.model['status_id'] = $scope.statusId;
                        $scope.$apply();
                        CRM.status(ts('Status updated.'));
                    });
                };

                $scope.setFieldFromModel();
                setTimeout(function() {$(".ci__case-info-item.statusField select").css($scope.$parent.getInputStyles()).select2();}, 0);
            }
        };
    });

    angular.module(moduleName).directive("caseClients", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/caseInfo/caseClients.html",
            scope: {model: "="},
            bindToController: true,
            controllerAs: "ctrl",
            controller: function($scope, $window) {
                $scope.generateStyles = $scope.$parent.generateStyles;
                $scope.toggleMode = $scope.$parent.toggleMode;
                $scope.setFieldFromModel = function() {$scope.clientId = $scope.ctrl.model['client_ids'][0];};
                $scope.editConfirm = function() {
                    $scope.$parent.editConfirm('new_case_client_id', $scope.clientId, 'clientsField', function(result) {
                        var message = '<ul>';
                        message += '<li>Case(id=' + $scope.ctrl.model['id'] + ') have been moved to the trash.</li>';
                        message += '<li>Created the same case(with activities) and new client.</li>';
                        message += '<li>You have been redireced to this new case.</li>';
                        message += '</ul>';
                        CRM.alert(message, 'Change case client', 'success');
                        $window.location.href = "#/supportcase/manage-case/" + result.values.case.id;
                    });
                };
                $scope.toggleClientDescription = function(clientClassName) {$('.ci__client.' + clientClassName).toggleClass('opened');};

                $scope.setFieldFromModel();
                setTimeout(function() {$(".ci__case-info-item.clientsField input").css($scope.$parent.getInputStyles()).crmEntityRef();}, 0);
            }
        };
    });

    angular.module(moduleName).directive("caseStartDate", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/caseInfo/caseStartDate.html",
            scope: {model: "="},
            bindToController: true,
            controllerAs: "ctrl",
            controller: function($scope) {
                $scope.toggleMode = $scope.$parent.toggleMode;
                $scope.setFieldFromModel = function() {$scope.startDate = $scope.ctrl.model['start_date'];};
                $scope.editConfirm = function() {
                    $scope.$parent.editConfirm('start_date', $scope.startDate, 'startDateField', function(result) {
                        $scope.ctrl.model['start_date'] = $scope.startDate;
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
            bindToController: true,
            controllerAs: "ctrl",
            controller: function($scope) {
                $scope.toggleMode = $scope.$parent.toggleMode;
                $scope.getEntityLabel = $scope.$parent.getEntityLabel;
                $scope.setFieldFromModel = function() {$scope.categoryId = $scope.ctrl.model['category_id'];};
                $scope.editConfirm = function() {
                    $scope.$parent.editConfirm('category_id', $scope.categoryId, 'categoryField', function(result) {
                        $scope.ctrl.model['category_id'] = $scope.categoryId;
                        $scope.$apply();
                        CRM.status(ts('Category updated.'));
                    });
                };

                $scope.setFieldFromModel();
                setTimeout(function() {$(".ci__case-info-item.categoryField select").css($scope.$parent.getInputStyles()).select2();}, 0);
            }
        };
    });

    angular.module(moduleName).directive("caseTags", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/caseInfo/caseTags.html",
            scope: {model: "="},
            bindToController: true,
            controllerAs: "ctrl",
            controller: function($scope) {
                $scope.toggleMode = $scope.$parent.toggleMode;
                $scope.generateStyles = $scope.$parent.generateStyles;
                $scope.setFieldFromModel = function() {$scope.caseTags = $scope.ctrl.model['tags_ids'];};
                $scope.editConfirm = function() {
                    var tagsIds = ($scope.caseTags === undefined) ? [] : $scope.caseTags;
                    $scope.$parent.editConfirm('tags_ids', tagsIds, 'tagsField', function(result) {
                        $scope.ctrl.model['tags_ids'] = $scope.caseTags;
                        CRM.status(ts('Tags updated.'));
                        $scope.$apply();
                    });
                };

                $scope.setFieldFromModel();

                var inputStyles =  $scope.$parent.getInputStyles();
                inputStyles['height'] = 'auto';
                setTimeout(function() {$(".ci__case-info-item.tagsField select").css(inputStyles).select2();}, 0);
            }
        };
    });

    angular.module(moduleName).directive("communication", function() {
        return {
            restrict: "E",
            templateUrl: "~/manageCase/directives/communication.html",
            scope: {model: "="},
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
            scope: {model: "="},
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
            scope: {model: "="},
            bindToController: true,
            controllerAs: "ctrl",
            controller: function($scope) {
                $scope.ts = CRM.ts();
                $scope.recentCases = [];
                $scope.updateRecentCases = function() {
                    CRM.api3('SupportcaseManageCase', 'get_recent_cases', {
                        "client_id": $scope.ctrl.model['recent_case_for_contact']['contact_id'],
                        "limit_per_page": 100,
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
            bindToController: true,
            controllerAs: "ctrl",
            controller: function($scope) {

            }
        };
    });

})(angular, CRM.$, CRM._);
