<template>

    <div>
        <ul class="nav nav-tabs nav-bar-custom">
            <li v-for="group in defaultGroups" class="">
                <a class="btn" @click="currentGroup = group"
                   :class="[{outlined: currentGroup === group}, groupColors[group]]"

                   :href="'#' + group">{{group | capitalize}}</a>
            </li>
        </ul>

        <div id="conversationController" class="conversation_container ">


            <div v-for="convo, index in sortedConversationList" :disabled="convo.disabled === true">

                <div class="card card_main tab-content"
                     :class="{activeConversation: convo.isActive }">
                    <div class="card-body ">
                        <div class="row">

                            <div class="col-lg-6">
                                <b>{{formatPhoneNumber(convo.recipient_phone_number)}}</b>


                                <span v-text="readableTimestamp(convo.last_message_timestamp)">Last Message: </span>

                                <div v-if="convo.unread_messages > 0">
                                    <img src="/images/icons/comments.png">
                                    <span style="font-size:10px;">New Messages: {{convo.unread_messages}}</span>
                                </div>
                            </div>
                            <div class="col-lg-6">

                                <button
                                        v-for="group in defaultGroups" v-if="group !== currentGroup"
                                        @click="updateConversation({'id': convo.id, 'group': group})"
                                        :class=" ' btn  btn-sm group-btn ' + groupColors[group] ">
                                    {{group | capitalize}}
                                </button>

                                <button @click="loadConversation(convo.conversation_id, index)"
                                        class="btn btn-success btn-sm ">View Chat
                                </button>
                            </div>
                        </div>

                    </div>

                </div>


            </div>
        </div>
    </div>


</template>

<script>
  import moment from 'moment';

  export default {

    props: ['userId'],

    filters: {
      capitalize: function(value) {
        if (!value) {
          return '';
        }
        value = value.toString();
        return value.charAt(0).toUpperCase() + value.slice(1);
      },
    },

    data() {
      return {
        defaultGroups: ['open', 'sold', 'ignore'],
        groupColors: {open: 'btn-primary', sold: 'btn-info', ignore: 'btn-basic'},
        currentGroup: 'open',
        conversationList: [],

        smsClient: {
          id: 0,
          secret: '',
        },

      };
    },

    mounted() {

      Event.$on('read-new-messages', payload => {
        if (this.conversationIds.includes(payload.conversationId)) {

          let index = this.conversationList.findIndex(item => {
            if (item !== undefined) {
              return item.conversation_id === payload.conversationId;
            }
          });

          this.conversationList[index].unread_messages = 0;

          this.refreshConversationList();
        }
      });

      this.getConversations(result => {
        this.conversationList = result.data;
        this.timer = setInterval(this.checkNewConversations, 2000);
      });

    },

    methods: {

      updateConversation(payload) {
        axios.patch('/sms/api/conversations' + this.isAdminLogin, payload).then(result => {
          let keys = Object.keys(payload);
          let index = this.conversationIds.indexOf(payload.id);
          for (let i = 0; i < keys.length; i++) {
            this.conversationList[index][keys[i]] = payload[keys[i]];
          }

        }).catch(result => {
        });
      },

      showGroup(group) {
        this.currentGroup = group;
      },

      readableTimestamp(timestamp) {
        return moment(timestamp).fromNow();
      },

      refreshConversationList() {
        let temp = this.conversationList.pop();
        this.conversationList.push(temp);
      },

      loadConversation(conversationId, index) {
        this.emitLoadMessages(conversationId);
        for (let i = 0; i <= this.sortedConversationList.length; i++) {
          if (this.sortedConversationList[i] === undefined) {
            continue;
          }
          if (i === index) {
            this.sortedConversationList[i]['isActive'] = true;
          }
          else {
            this.sortedConversationList[i]['isActive'] = false;
          }
        }
      },

      emitLoadMessages(conversationId) {
        Event.$emit('load-messages', {
          'conversationId': conversationId,
        });
      },

      checkNewConversations() {

        this.getConversations(result => {

          let conversations = result.data;

          for (let i = conversations.length; i >= 0; i--) {
            let convo = conversations[i];
            if (convo === undefined) {
              continue;
            }

            // new conversations found, push to list

            if (this.recipientPhoneIds.includes(convo.recipient_phone_id) === false) {
              this.conversationList.push(convo);
            }
            else {

              //update conversation's stats
              let index = this.conversationList.findIndex(element => {
                return element.recipient_phone_id === convo.recipient_phone_id;
              });

              this.conversationList[index].group = convo.group;

              this.conversationList[index].last_message_timestamp = convo.last_message_timestamp;

              this.conversationList[index].unread_messages = convo.unread_messages;

              if (this.conversationList[index].unread_messages > 0) {

                Event.$emit('new-messages', {'conversationId': convo.conversation_id});

              }

            }
          }

        });
      },

      getConversations(callback) {
        axios.get('/sms/api/conversations' + this.isAdminLogin).then(callback);
      },

      formatPhoneNumber(phone) {

        phone = phone.replace(/[^\d]/g, '');

        if (phone.length === 11) {
          return phone.replace(/(\d{1})(\d{3})(\d{3})(\d{4})/, '+$1 ($2) $3-$4');
        }
        return phone;
      },

    },

    computed: {

      isAdminLogin() {
        let isAdminLogin = '';
        if (window.location.href.indexOf('adminLogin') > -1) {
          isAdminLogin = '?adminLogin';
        }

        return isAdminLogin;
      },

      conversationIds() {
        let ids = [];

        this.conversationList.forEach(item => {
          if (item !== undefined) {
            ids.push(item.conversation_id);
          }
        });

        return ids;
      },

      recipientPhoneIds() {
        let ids = [];

        this.conversationList.forEach(item => {
          if (item !== undefined) {
            ids.push(item.recipient_phone_id);
          }
        });

        return ids;
      },

      sortedConversationList() {
        let sorted = [];
        for (let i = 0; i < this.conversationList.length; i++) {
          if (this.conversationList[i].group === this.currentGroup) {
            sorted.push(this.conversationList[i]);
          }
        }

        return sorted.slice().sort((a, b) => {
          return moment(b.last_message_timestamp, 'YYYY-MM-DD H:mm:ss').format('x') -
              moment(a.last_message_timestamp, 'YYYY-MM-DD H:mm:ss').format('x');
        });

      },

    },

  };
</script>

<style scoped>


    .nav-bar-custom .nav-item .nav-link {
    }

    .group-btn {
        margin: 2px;
    }

    .activeConversation {
        background-color: #bfbfbf;
    }

    .conversation_container {
        max-height: 750px;
        overflow-y: scroll;
    }

    .card_main {
        background-color: white;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
        margin: 7px;
        font-size: 14px;
    }

    .card_main h5 {
        font-size: 16px;
    }

    .card-body {
        padding: 10px;
    }

    .outlined {
        border: 2px solid black;
    }

</style>