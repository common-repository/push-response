(function () {

  var FORM_SELECTOR = '.pushresponse-settings-form',
    ADD_LIST_BUTTON_SELECTOR = '.pushreponse-add-list-button',
    REMOVE_LIST_BUTTON_SELECTOR = '.pushresponse-remove-list-button',
    LISTS_DATA_INPUT_SELECTOR = '.pushresponse-lists-data',
    NEW_LIST_ID_INPUT_SELECTOR = '.pushresponse-new-list-id',

    ID_PLACEHOLDER = ':id',

    CHECK_IF_PRO_URL = 'https://messageresponse.net/campaigns/' + ID_PLACEHOLDER + '/check_owner',

    LIST_DATA_URL = 'https://messageresponse.net/campaigns/' + ID_PLACEHOLDER + '/responder_json',

    ENTER_KEY_CODE = 13,

    ACCESS_DENIED_MESSAGE = 'To use this plugin you have to be Push Response PRO user. You can upgrade to PRO at https://pushresponse.net',

    form = document.querySelector(FORM_SELECTOR),
    addListButton = document.querySelector(ADD_LIST_BUTTON_SELECTOR),
    removeListButtons = document.querySelectorAll(REMOVE_LIST_BUTTON_SELECTOR),
    listsDataInput = document.querySelector(LISTS_DATA_INPUT_SELECTOR),
    newListIdInput = document.querySelector(NEW_LIST_ID_INPUT_SELECTOR);

  attachEventListeners();

  function attachEventListeners() {
    addListButton.addEventListener('click', addList);

    Array.prototype.forEach.call(removeListButtons, function (removeListButton) {
      removeListButton.addEventListener('click', removeList);
    });

    newListIdInput.addEventListener('keypress', function (event) {
      if (event.keyCode === ENTER_KEY_CODE) {
        event.preventDefault();
        addList();
      }
    });
  }

  function getLists() {
    var lists;

    try {
      lists = JSON.parse(listsDataInput.value);

      if (lists.length !== undefined) {
        lists = {};
      }

      return lists || {};
    } catch (error) {
      return {};
    }
  }

  function addList() {
    var listId = newListIdInput.value.trim(),
      checkIfUserIsProUrl = CHECK_IF_PRO_URL.replace(ID_PLACEHOLDER, listId),
      fetchListUrl = LIST_DATA_URL.replace(ID_PLACEHOLDER, listId);

    if (!listId) {
      return;
    }

    checkIfListOwnerIsPro(listId, function (pro) {
      if (pro) {
        fetchList(listId, function (list) {
          var lists = getLists();

          list.id = listId;
          lists[list.id] = list;

          submitListsData(lists);
        })
      } else {
        alert(ACCESS_DENIED_MESSAGE);
      }
    });
  }

  function fetchList(listId, callback) {
    var url = LIST_DATA_URL.replace(ID_PLACEHOLDER, listId);

    makeRequest('GET', url, callback, alert);
  }

  function checkIfListOwnerIsPro(listId, callback) {
    var url = CHECK_IF_PRO_URL.replace(ID_PLACEHOLDER, listId);

    makeRequest('GET', url, function (response) {
      callback(response.pro);
    }, alert);
  }

  function removeList() {
    var listId = this.getAttribute('data-id'),
      lists = getLists();

    if (lists[listId]) {
      delete lists[listId];
    }

    submitListsData(lists);
  }

  function submitListsData(lists) {
    listsDataInput.value = JSON.stringify(lists);

    form.submit();
  }

  function makeRequest(requestType, requestUrl, onSuccess, onFail) {
    var request = new XMLHttpRequest();

    request.open(requestType, requestUrl, true);
    request.setRequestHeader('Accept', 'application/json');
    request.send();

    request.onreadystatechange = function () {
      if (request.readyState === 4) {
        try {
          var data = JSON.parse(request.responseText);

          if (request.status === 200) {
            if (data.error) {
              return onFail(data.error);
            }

            return onSuccess(data);
          }

          return onFail(data);
        } catch (error) {
          onFail(request.responseText);
        }
      }
    };
  }
})();
