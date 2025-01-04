/****
 * Glabal Variables
 */

let temporaryMsgId = 0;

const pusherKey = import.meta.env.VITE_PUSHER_APP_KEY;
const pusherCluster = import.meta.env.VITE_PUSHER_APP_CLUSTER;
const channelName = import.meta.env.VITE_CHANNEL_NAME;

const messageForm = $(".message-form"),
    messageInput = $(".message-input"),
    crsf_token = $('meta[name="csrf-token"]').attr("content"),
    chatBoxContainer = $(".wsus__chat_area_body"),
    messengerContactBox = $(".messenger-contacts"),
    auth_id = $('meta[name="auth_id"]').attr("content"),
    url = $('meta[name="url"]').attr("content");

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
            $(".wsus__chat_info_gallery").html("");
            //load gallery
            if (data?.shared_photos) {
                $(".nothing_share").addClass("d-none");

                $(".wsus__chat_info_gallery").html(data.shared_photos);
            } else {
                $(".nothing_share").removeClass("d-none");
            }

            if (data.favorite > 0) {
                $(".star").addClass("active");
            } else {
                $(".star").removeClass("active");
            }
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
                $(".no_messages").addClass("d-none");
                cancelAttachment();
                makeSeen(true);
            },
            success: function (data) {
                updateContactItem(getMessengerId());
                getOnlineStatus();
                const tempMsgCardElement = chatBoxContainer.find(
                    `.message-card[data-id=${data.tempID}]`
                );
                tempMsgCardElement.before(data.message);
                tempMsgCardElement.remove();
                const myEvent = new Event("messageSent");
                document.dispatchEvent(myEvent);
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

function recieveMessageCard(e) {
    if (e.attachment) {
        return `<div class="wsus__single_chat_area message-card" data-id="${
            e.id
        }">
          <div class="wsus__single_chat ">
            <a class="venobox" data-gall="gallery${e.id}" href="${
            url + e.attachment
        }">
              <img src="${url + e.attachment}" alt="" class="img-fluid w-100" />
               </a>
            </div>
            ${
                e.body != null && e.body.length > 0
                    ? `<p class="messages">${e.body}</p>`
                    : ""
            }
          </div>
        </div>
      </div>`;
    } else {
        return ` <div class="wsus__single_chat_area message-card" data-id="${e.id}">
          <div class="wsus__single_chat ">
            <p class="messages">${e.body}</p>
          </div>
        </div>`;
    }
}

function cancelAttachment() {
    $(".attachment-block").addClass("d-none");
    $(".attachment-input").val("");
    $(".attachment-preview").attr("src", "");
    $(".emojionearea-editor").text("");
    $("input[type='file']").val(null);
    messageForm.trigger("reset");
    var emojiElt = $("#example1").emojioneArea();
    emojiElt.data("emojioneArea").setText("");
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
                messengerContactBox.find(".no_contact").remove();
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
/*****
 *
 * Favorite
 */

function star(user_id) {
    $.ajax({
        method: "POST",
        url: "messenger/favorite",
        data: {
            _token: crsf_token,
            user_id: user_id,
        },
        success: function (data) {
            //updateContactItem(user_id);
            if (data.status == "added") {
                /*
                $.ajax({
                    method: "GET",
                    url: "messenger/fetch-favorites",
                    data: {},
                    success: function (data) {
                        $(".favourite_user_slider").html(data.favorite_list);
                    },
                    error: function (xhr, status, error) {
                        console.log(error);
                    },
                });
*/
                notyf.success("Added to favorite ");
            } else if (data.status == "removed") {
                /*
                $.ajax({
                    method: "GET",
                    url: "messenger/fetch-favorites",
                    data: {},
                    success: function (data) {
                        $(".favourite_user_slider").html(data.favorite_list);
                    },
                    error: function (xhr, status, error) {
                        console.log(error);
                    },
                });
*/
                notyf.success("Removed from favorite ");
            }
        },
        error: function (xhr, status, error) {
            console.log(error);
        },
    });
}

/*****
 *get Favorite users
 *
 */
/*
function fetchFavoriteList() {
    $.ajax({
        method: "GET",
        url: "messenger/fetch-favorites",
        data: {},
        success: function (data) {
            $(".favourite_user_slider").html(data.favorite_list);
        },
        error: function (xhr, status, error) {
            console.log(error);
        },
    });
}
*/
/****
 *
 *Delete messages
 *
 */

function deleteMessage(id) {
    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                method: "DELETE",
                url: "messenger/delete-message",
                data: {
                    _token: crsf_token,
                    id: id,
                },
                beforeSend: function () {
                    $(`.message-card[data-id="${id}"]`).remove();
                },
                success: function (data) {
                    if (data.success == false) {
                        Swal.fire({
                            title: "Canceled!",
                            text: "Your cannot delete others messages.",
                            icon: "error",
                        });
                    } else if (data.success == true) {
                        Swal.fire({
                            title: "Deleted!",
                            text: "Your file has been deleted.",
                            icon: "success",
                        });
                        updateContactItem(getMessengerId());
                    }
                },
                error: function (xhr, status, error) {
                    console.log(error);
                },
            });
        }
    });
}

/****
 *
 *
 * Relaltime listener
 */

// Initialize Pusher with your app key and cluster

const pusher = new Pusher(pusherKey, {
    cluster: pusherCluster,
});

// Subscribe to a channel message channel
const channel = pusher.subscribe(`${channelName}` + auth_id);

// Listen for an event on the channel message channel
channel.bind("MessageSent", function (data) {
    if (getMessengerId() != data.from_id) {
        updateContactItem(data.from_id);
        playNotificationSound();
    }

    // Handle the event data and display the message
    //console.log("Received message:", data);
    let message = recieveMessageCard(data);
    if (getMessengerId() == data.from_id) {
        chatBoxContainer.append(message);
        scrollToBottom(chatBoxContainer);
    }
});

//listen to online Channel

// Subscribe to the 'user.online' channel to listen for online users
const onlineChannel = pusher.subscribe("user.online");

// Bind to the 'onlineUser' event and log the received data
let activeUsers = new Set(); // Track currently active users

function toggleActive(data) {
    const newActiveUsers = new Set(data); // Create a set from the new user list

    // Handle users in the new list
    for (let user of newActiveUsers) {
        let contactMember = $(`.messenger-list-item[data-id="${user}"]`)
            .find(".img")
            .find("span");

        contactMember.removeClass("inactive");
        contactMember.addClass("active");
    }

    // Handle users no longer in the list
    for (let user of activeUsers) {
        if (!newActiveUsers.has(user)) {
            let contactMember = $(`.messenger-list-item[data-id="${user}"]`)
                .find(".img")
                .find("span");
            contactMember.removeClass("active");
            contactMember.addClass("inactive");
        }
    }

    // Update the activeUsers set
    activeUsers = newActiveUsers;
}

onlineChannel.bind("onlineUser", function (data) {
    console.log(`this is  ${data}`);
    toggleActive(data);
});

// Subscribe to the 'user.loggedin' channel to listen for logged-in users
const loggedInChannel = pusher.subscribe("user.loggedin");

// Bind to the 'LoggedIN' event and log the received data
loggedInChannel.bind("LoggedIN", function (data) {
    //console.log("LoggedIN", data);
});

/****
 *
 *
 * play message sound
 */
async function getOnlineStatus() {
    await $.ajax({
        method: "GET",
        url: "messenger/fetch-online-status",
        data: {
            _token: crsf_token,
            id: auth_id,
        },
        success: function (data) {
            toggleActive(data.onlineUsers);
        },
        error: function (xhr, status, error) {
            console.log(error);
        },
    });
}
function playNotificationSound() {
    const sound = new Audio(`/default/message-sound.mp3`);
    sound.play();
}

window.addEventListener("beforeunload", (event) => {
    $.ajax({
        method: "DELETE",
        url: "messenger/delete-online-status",
        data: {
            _token: crsf_token,
            id: auth_id,
        },
        success: function (data) {
            $.ajax({
                method: "GET",
                url: "messenger/fetch-online-status",
                data: {
                    _token: crsf_token,
                    id: auth_id,
                },
                success: function (data) {
                    console.log(data);
                },
                error: function (xhr, status, error) {
                    console.log(error);
                },
            });
        },
        error: function (xhr, status, error) {
            console.log(error);
        },
    });
});

/********************************** */

/***************************** */
/*
document.addEventListener("visibilitychange", () => {
    if (document.visibilityState === "hidden") {
        // Tab is hidden (user switched tabs or minimized window)
        console.log("Tab is hidden");
    } else if (document.visibilityState === "visible") {
        // Tab is visible again
        console.log("Tab is visible");
    }
});
*/

/********************************** */
// On DOM load

/*------------------------------*/
$(document).ready(function () {
    getContacts();
    setTimeout(() => {
        getOnlineStatus();
    }, 500);

    //fetchFavoriteList();
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
    cancelAttachment();
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

// favorite add or remove click

$(".star").on("click", function (e) {
    e.preventDefault();
    $(this).toggleClass("active");
    star(getMessengerId());
});
/*****
 *
 * delete message
 */
$("body").on("click", ".dlt-message", function (e) {
    let id = $(this).data("id");
    e.preventDefault();
    deleteMessage(id);
});
