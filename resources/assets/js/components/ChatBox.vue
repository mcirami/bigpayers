<template>
    <div id="chatbox">

        <div class="card" style="width: 35rem; ">
            <div class="card-body" style="">
                <!--<h5 class="card-title">Card title</h5>-->
                <div id="messageContainer" class="scrollbar"
                     style="overflow-y: scroll; margin-bottom:20px; max-height:500px;">
                    <div v-for="message in messages" class="card" style="border:none;">
                        <div class="message">
                            <span v-if="message.sending_phone_number_id === conversation.service_phone_id"
                                  style="font-size:10px; color:#0069D9;">Me:</span>
                            <span v-if="message.sending_phone_number_id !== conversation.service_phone_id"
                                  style="font-size:10px;">{{formatPhoneNumber(message.sendingNumber)}}:</span>
                            <br>
                            <span v-if="message.sending_phone_number_id === conversation.service_phone_id"
                                  style="color:#0069D9;">{{message.message}}</span>
                            <span v-if="message.sending_phone_number_id !== conversation.service_phone_id"
                            >{{message.message}}</span>
                            <span style="font-size:10px; float:right;">{{message.created_at}}</span>
                            <a v-if="message.mediaUrl" :href="message.mediaUrl" target="_blank"><img
                                    class="mediaImg" :src="message.mediaUrl"
                                    alt="Failed to load media type, might be a video."></a>
                        </div>
                    </div>


                </div>

                <div class="form-group">
                    <span v-if="unreadMessages"><a @click="scrollChatDown" href="#">New messages! - Click here to scroll down</a></span>
                    <textarea v-model="message" id="messageTextArea" @keyup="keyMonitor" class="form-control"
                              placeholder="Write a Reply..."></textarea>

                    <button @click="sendMessage" id="replyButton" class="btn btn-sm btn-success btn-block btn-gray"
                            type="submit">Reply
                    </button>

                </div>


                <div id="imageContainer">
                    <label class="value_span9">Add Image</label>
                    <div class="input-group ">
                        <input class="form-control " id="image" type="file" name="image" accept=""><br>
                    </div>
                </div>
            </div>

        </div>

    </div>

</template>

<script>
  import moment from 'moment';

  export default {
    name: 'ChatBox',

    computed: {
      isAdminLogin() {
        let isAdminLogin = '';
        if (window.location.href.indexOf('adminLogin') > -1) {
          isAdminLogin = '?adminLogin';
        }

        return isAdminLogin;
      },

    },

    data() {
      return {
        messages: [],
        message: '',

        conversation: {},

        unreadMessages: false,

      };
    },

    mounted() {

      Event.$on('load-messages', (payload) => {

        // load messages first so it's faster
        this.loadMessages(payload.conversationId);

        axios.get('/sms/api/conversations/' + payload.conversationId + this.isAdminLogin).then(result => {
          this.conversation = result.data;
        });

        this.scrollChatDown();
      });

      Event.$on('new-messages', (payload) => {
        if (payload.conversationId === this.conversation.id) {
          this.unreadMessages = true;
          this.loadMessages(payload.conversationId);
        }
      });

    },

    created() {

    },

    methods: {

      readableTimestamp(timestamp) {
        return moment(timestamp).fromNow();
      },

      loadConversation(id) {
        axios.get('/sms/api/conversations/' + id + this.isAdminLogin).then(payload => {
          this.conversation = payload.data;
        });
      },

      loadMessages(conversationId) {

        axios.get('/sms/api/conversations/' + conversationId + '/messages' + this.isAdminLogin).
            then(result => {
              this.messages = result.data;

              Event.$emit('read-new-messages', {'conversationId': conversationId});

              axios.patch('/sms/api/conversations/' + conversationId + '/read-new-messages' + this.isAdminLogin).
                  then(result => {});
            });
      },

      formatPhoneNumber(phone) {

        phone = phone.replace(/[^\d]/g, '');

        if (phone.length === 11) {
          return phone.replace(/(\d{1})(\d{3})(\d{3})(\d{4})/, '+$1 ($2) $3-$4');
        }
        return phone;
      },

      scrollChatDown() {

        setTimeout(() => {
          $('#messageContainer').animate({
            scrollTop: $('#messageContainer')[0].scrollHeight,
          }, 'slow');
        }, 500);
        this.unreadMessages = false;

      },

      keyMonitor(event) {
        if (event.key === 'Enter') {
          this.sendMessage();
        }
      },

      sendMessage() {
        let tempMessage = this.message;
        this.message = '';

        if (tempMessage.indexOf('http') > -1) {
          // tempMessage += ' ;)';
        }

        let data = new FormData();

        if (document.getElementById('image').value == '' && tempMessage == '') {
          return;
        }

        data.append('image', document.getElementById('image').files[0]);

        document.getElementById('image').value = '';

        data.append('fromPhoneNumberId', this.conversation.service_phone_id);
        data.append('toPhoneNumberId', this.conversation.recipient_phone_id);

        data.append('message', tempMessage);

        axios.post('/sms/api/messages/send' + this.isAdminLogin, data, this.axiosConfig).then((result) => {

          this.loadMessages(this.conversation.id);
          this.scrollChatDown();
        }).catch(response => console.log(response));
      },

    },

  };
</script>

<style scoped>

    .mediaImg {
        height: auto;
        width: 50%;
    }

    .card p {
        font-size: 12px;
        background-color:white;
    }

    .message {
        background-color:white;
        padding: 5px;
        border-bottom: 1px #c6c6c6 solid;
    }

    .sent_message {
        color: red;
    }

    .received_message {
        color: green;
    }

</style>