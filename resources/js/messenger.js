/****
 * Glabal Variables
 */

let temporaryMsgId = 0;

const messageForm = $(".message-form"),
    messageInput = $(".message-input"),
    crsf_token = $('meta[name="csrf-token"]').attr("content"),
    chatBoxContainer = $(".wsus__chat_area_body"),
    messengerContactBox = $(".messenger-contacts"),
    auth_id = $('meta[name="auth_id"]').attr("content");

const getMessengerId = () => {
    return $("meta[name=id]").attr("content");
};

const setMessengerId = (id) => {
    $("meta[name=id]").attr("content", id);
};

/**
 * ------------------------------------------------------------
 * Reusable Messenger Component function
 * ------------------------------------------------------------
 */
function enableChatBoxLoader() {
    $(".overlay-chat").removeClass("d-none");
}

function disabledChatBoxLoader() {
    $(".overlay-chat").addClass("d-none");
}

function imagePreview(input, selector) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            $(selector).attr("src", e.target.result);
        };
        reader.readAsDataURL(input.files[0]);
    }
}
let page = 1;
let noMoreDataSearch = false;
let searchTempValue = "";
let setSearchLoading = false;
function searchUsers(query) {
    if (query !== searchTempValue) {
        page = 1;
        noMoreDataSearch = false;
    }
    searchTempValue = query;
    if (!setSearchLoading && !noMoreDataSearch) {
        $.ajax({
            method: "GET",
            url: "messenger/search",
            data: {
                query: query,
                page: page,
            },
            beforeSend: function () {
                setSearchLoading = true;
                let loader = `  <div class="text-center search-loader">
            <div class="spinner-border text-primary " role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>`;
                $(".user_search_list_result").append(loader);
            },
            success: function (data) {
                if (data.records === "") {
                    $(".user_search_list_result").html(data.records);
                }
                $(".search-loader").remove();
                setSearchLoading = false;

                if (page < 2) {
                    $(".user_search_list_result").html(data.records);
                } else {
                    $(".user_search_list_result").append(data.records); //html(data.records)
                }
                noMoreDataSearch = page >= data?.last_page;
                if (!noMoreDataSearch) {
                    page++;
                }
            },
            error: function (xhr, status, error) {
                console.log(xhr);
            },
        });
    }
}

function debounce(callback, wait) {
    let timerId;
    return function (...args) {
        clearTimeout(timerId);
        timerId = setTimeout(() => {
            callback.apply(this, args);
        }, wait);
    };
}

function actionOnScroll(selector, callback, topScroll = false) {
    $(selector).on("scroll", function () {
        let element = $(this).get(0);
        const condition = topScroll
            ? element.scrollTop === 0
            : element.scrollTop + element.clientHeight >= element.scrollHeight;

        if (condition) {
            callback();
        }
    });
}

/***
 *
 * fetch id data of user and update the view
 *
 */

function IDinfo(id) {
    $.ajax({
        method: "GET",
        url: "messenger/id-info",
        data: { id: id },
        beforeSend: function () {
            $(".wsus__chat_area_body").removeClass("d-none");
            $(".wsus__chat_area_header").removeClass("d-none");
            $(".wsus__chat_area_footer").removeClass("d-none");
            NProgress.start();
            enableChatBoxLoader();
        },
        success: function (data) {
            fetchMessages(data.fetch.id, true);
            $(".messenger-header").find("img").attr("src", data.fetch.avatar);
            $(".messenger-header").find("h4").text(data.fetch.name);
            $(".messenger-info-view .user_photo")
                .find("img")
                .attr("src", data.fetch.avatar);

            $(".messenger-info-view ").find("h3").text(data.fetch.name);
            $(".messenger-info-view .user_unique_name").text(
                data.fetch.username
            );
            NProgress.done();
            disabledChatBoxLoader();
        },

        error: function (xhr, status, error) {
            disabledChatBoxLoader();
        },
    });
}

/******
 *
 *
 * send message
 */

function sendMessage() {
    temporaryMsgId++;
    let tempID = `temp_${temporaryMsgId}`;
    //let hasAttachment = !!$(".attachment-input").val();
    let hasAttachment = $(".attachment-input").val() ? true : false;
    const inputvalue = messageInput.val();

    if (inputvalue.length > 0 || hasAttachment) {
        const formData = new FormData($(".message-form")[0]);
        formData.append("id", getMessengerId());
        formData.append("temporaryMsgId", tempID);
        formData.append("_token", crsf_token);

        $.ajax({
            method: "POST",
            url: "messenger/send-message",
            data: formData,
            dataType: "JSON",
            processData: false,
            contentType: false,
            beforeSend: function () {
                if (hasAttachment) {
                    scrollToBottom(chatBoxContainer);
                    chatBoxContainer.append(
                        sendTempMessageCard(tempID, inputvalue, hasAttachment)
                    );
                } else {
                    scrollToBottom(chatBoxContainer);
                    chatBoxContainer.append(
                        sendTempMessageCard(tempID, inputvalue, hasAttachment)
                    );
                }
                /*
                messageForm.trigger("reset");
                $(".emojionearea-editor").text("");
                */
                cancelAttachment();
            },
            success: function (data) {
                updateContactItem(getMessengerId());
                const tempMsgCardElement = chatBoxContainer.find(
                    `.message-card[data-id=${data.tempID}]`
                );
                tempMsgCardElement.before(data.message);
                tempMsgCardElement.remove();
            },
            error: function (xhr, status, error) {},
        });
    }
}

function sendTempMessageCard(tempId, message, hasAttachment) {
    if (hasAttachment) {
        return `<div class="wsus__single_chat_area message-card" data-id="${tempId}">
          <div class="wsus__single_chat chat_right">
            <div class="pre_loader">
              <div class="spinner-border text-light" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>
            ${message.length > 0 ? `<p class="messages">${message}</p>` : ""}
            <span class="clock"><i class="fas fa-clock"></i> now</span>
            <a class="action" href="#"><i class="fas fa-trash"></i></a>
          </div>
        </div>
      </div>`;
    } else {
        return ` <div class="wsus__single_chat_area message-card" data-id="${tempId}">
          <div class="wsus__single_chat chat_right">
            <p class="messages">${message}</p>
            <span class="clock"><i class="fas fa-clock"></i> now</span>
            <a class="action" href="#"><i class="fas fa-trash"></i></a>
          </div>
        </div>`;
    }
}

function cancelAttachment() {
    $(".attachment-block").addClass("d-none");
    $(".attachment-input").val("");
    $(".attachment-preview").attr("src", "");
    $(".emojionearea-editor").text("");
    messageForm.trigger("reset");
}

function showChatbox() {
    $(".black").addClass("d-none");
    $(".wsus__chat_area_body").removeClass("d-none");
    $(".wsus__chat_area_header").removeClass("d-none");
    $(".wsus__chat_area_footer").removeClass("d-none");

    return true;
}

function hideChatbox() {
    cancelAttachment();
    $(".black").removeClass("d-none");
    $(".wsus__chat_area_body").addClass("d-none");
    $(".wsus__chat_area_header").addClass("d-none");
    $(".wsus__chat_area_footer").addClass("d-none");

    return true;
}
export { showChatbox, hideChatbox };

/***
 * fetch messeges from database
 */
let messegesPage = 1;
let noMoreMessages = false;
let messegesLoading = false;
function fetchMessages(id, newFetch = false) {
    if (newFetch) {
        messegesPage = 1;
        noMoreMessages = false;
    }
    if (!messegesLoading && !noMoreMessages) {
        $.ajax({
            method: "GET",
            url: "messenger/fetch-messages",
            data: {
                _token: crsf_token,
                id: id,
                page: messegesPage,
            },
            beforeSend: function () {
                messegesLoading = true;
                let loader = `  <div class="text-center messages-loader">
            <div class="spinner-border text-primary " role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>`;
                chatBoxContainer.prepend(loader);
            },
            success: function (data) {
                messegesLoading = false;
                chatBoxContainer.find(".messages-loader").remove();
                //make messeges seen
                makeSeen(true);
                if (messegesPage < 2) {
                    chatBoxContainer.html(data.messages);
                    scrollToBottom(chatBoxContainer);
                } else {
                    const lastMsg = $(chatBoxContainer)
                        .find(".message-card")
                        .first();
                    const curOffset =
                        lastMsg.offset().top - chatBoxContainer.scrollTop();

                    chatBoxContainer.prepend(data.messages);
                    chatBoxContainer.scrollTop(
                        lastMsg.offset().top - curOffset
                    );
                    //scrollToBottom(chatBoxContainer);
                }
                noMoreMessages = messegesPage >= data?.last_page;
                if (!noMoreMessages) {
                    messegesPage++;
                }
            },
            error: function (xhr, status, error) {
                console.log(error);
            },
        });
    }
}
/******
 *
 *
 * fetch conatct list from database
 *
 *
 *
 *
 */

let contactPage = 1;
let noMoreContacts = false;
let contactLoading = false;
function getContacts() {
    if (!contactLoading && !noMoreContacts) {
        $.ajax({
            method: "GET",
            url: "messenger/fetch-contacts",
            data: {
                _token: crsf_token,
                page: contactPage,
            },
            beforeSend: function () {
                contactLoading = true;
                let loader = `  <div class="text-center messages-loader">
            <div class="spinner-border text-primary " role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>`;
                $(".messenger-contacts").append(loader);
            },
            success: function (data) {
                contactLoading = false;
                $(".messages-loader").remove();
                if (contactPage < 2) {
                    messengerContactBox.html(data.contacts);
                } else {
                    messengerContactBox.append(data.contacts);
                }

                noMoreContacts = contactPage >= data?.last_page;
                if (!noMoreContacts) {
                    contactPage++;
                }
            },
            error: function (xhr, status, error) {
                contactLoading = false;
                //$(".messages-loader").remove();
                console.log(error);
            },
        });
    }
}
/***
 * scroll down to bottom on action
 */
function scrollToBottom(container) {
    $(container)
        .stop()
        .animate({
            scrollTop: $(container)[0].scrollHeight,
        });
}
/***
 * Update conatct item
 */
function updateContactItem(user_id) {
    if (user_id !== auth_id) {
        $.ajax({
            method: "GET",
            url: "messenger/update-contact-item",
            data: {
                _token: crsf_token,
                user_id: user_id,
            },
            success: function (data) {
                messengerContactBox
                    .find(`.messenger-list-item[data-id="${user_id}"]`)
                    .remove();
                messengerContactBox.prepend(data.contactItem);
                if (user_id == getMessengerId()) {
                    updateSelectedContent(user_id);
                }
            },
            error: function (xhr, status, error) {
                console.log(error);
            },
        });
    }
}
function updateSelectedContent(user_id) {
    $("body").find(".messenger-list-item").removeClass("active");
    $("body")
        .find(`.messenger-list-item[data-id="${user_id}"]`)
        .addClass("active");
}

/***
 * make messages seen
 */
function makeSeen() {
    $(`.messenger-list-item[data-id="${getMessengerId()}"]`)
        .find(".unseen_count")
        .remove();

    $.ajax({
        method: "POST",
        url: "messenger/make-seen",
        data: {
            _token: crsf_token,
            id: getMessengerId(),
        },
        success: function (data) {},
        error: function (xhr, status, error) {
            console.log(error);
        },
    });
}
/**
 * ------------------------------------------------------------
 * On Dom Load
 * ------------------------------------------------------------
 */
getContacts();
/*------------------------------*/
$(document).ready(function () {
    if (window.innerWidth < 768) {
        $("body").find(".go_home").addClass("d-none");
        $("body").on("click", ".messenger-list-item", function () {
            $(".wsus__user_list").addClass("d-none");
        });

        $("body").on("click", ".back_to_list", function () {
            $(".wsus__user_list").removeClass("d-none");
        });
    }

    $("#select_file").change(function () {
        imagePreview(this, ".profile-image-preview");
    });
    const debouncedSearch = debounce(function () {
        const value = $(".user_search").val();
        searchUsers(value);
    }, 500);
    $(".user_search").on("keyup", function () {
        let query = $(this).val();
        if (query.length > 0) {
            debouncedSearch();
        }
    });
});

/*
 * close search list if clicked ioutside it
 *
 */
$(document).on("click", (event) => {
    if (!$(event.target).closest(".user_search").length) {
        $("#search-input").val(""); // Use .val() to set the input value in jQuery
        $(".user_search_list_result").html("");
        page = 1;
    }
});

/**
 *
 *search pagtiantion
 *
 * */

actionOnScroll(".user_search_list_result", function () {
    let value = $(".user_search").val();
    searchUsers(value);
});

//click action for list-item
$("body").on("click", ".messenger-list-item", function () {
    const userId = $(this).data("id");
    updateSelectedContent(userId);
    setMessengerId(userId);
    IDinfo(userId);
    $("#search-input").val(""); // Use .val() to set the input value in jQuery
    $(".user_search_list_result").html("");
    page = 1;
});

//send mesage
$(".message-form").on("submit", function (e) {
    e.preventDefault();
    sendMessage();
});

$(".attachment-input").change(function () {
    imagePreview(this, ".attachment-preview");
    $(".attachment-block").removeClass("d-none");
});

$(".cancel-attachment").on("click", function () {
    cancelAttachment();
});

/***
 * click on title return chat page
 */
$(".main-app-header").on("click", function () {
    if (showChatbox()) {
        hideChatbox();
    }
});
$(".go_home").on("click", function () {
    if (showChatbox()) {
        hideChatbox();
    }
});

// message pagination
actionOnScroll(
    ".wsus__chat_area_body",
    function () {
        fetchMessages(getMessengerId());
    },
    true
);

//conatcats pagination

actionOnScroll(".messenger-contacts", function () {
    getContacts();
});
