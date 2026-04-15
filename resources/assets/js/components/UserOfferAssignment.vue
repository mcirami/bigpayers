<template>
    <div>

        <span class="small_txt value_span10">Assigned {{userType}}</span>
        <select @keyup.ctrl.65="doSomething()" multiple class="form-control input-sm" id="users" name="users[]">
            <option @click="moveToUnAssigned(index)" v-for="(user, index) in assignedUsersSorted"
                    :value="user.id">
                {{user.name}}
            </option>
        </select>
        <input type="text" maxlength="25" id="assigned" :placeholder="'Search for '+ userType + '...'"
               v-model="assignedUsersFilter"/>


        <span class="small_txt value_span10">UnAssigned {{userType}} </span>
        <select @keyup.ctrl.65="moveAllToAssigned()" multiple class="form-control input-sm " id="notAssigned"
                name="notAssigned">
            <option @click="moveToAssigned(index)" v-for="(user, index) in unAssignedUsers" :value="user.id">
                {{user.name}}
            </option>
        </select>

        <input type="text" id="unAssigned" maxlength="25" :placeholder="'Search for '+ userType + '...'"/>

        <span class="small_txt value_span10">To select more than one user, hold CTRL and click. To select from a range, hold shift.</span>
    </div>
</template>

<script>
  export default {
    name: 'UserOfferAssignment',

    props: {offerId: 0},

    data() {
      return {
        userType: 'Affiliates',
        assignedUsers: [],
        unAssignedUsers: [],
        assignedUsersFilter: '',
        unAssignedUsersFilter: '',

      };
    },

    mounted() {
      if (this.offerId !== undefined) {
        axios.get('/offer/assignedUsers/' + this.offerId).then(result => {
          this.assignedUsers = result.data;
        });
      }
      axios.get('/offer/assignableUsers').then(result => {
        //TODO FILTER ALL USERS FROM ASSIGNED USERS
        if (this.assignedUsers.empty) {

        }
        this.unAssignedUsers = result.data;
      });
    },

    computed: {
      assignedUsersSorted() {
        if (this.assignedUsersFilter === '') {
          return this.assignedUsers;
        }
        return this.assignedUsers.filter(thingy => {
          return thingy.name.toLowerCase().indexOf(this.assignedUsersFilter.toLowerCase()) > -1;
        });
      },
    },

    methods: {
      moveToUnAssigned(assignedUserIndex) {
        let user = this.assignedUsers[assignedUserIndex];
        this.assignedUsers.splice(assignedUserIndex, 1);
        this.unAssignedUsers.push(user);
      },

      moveToAssigned(unAssignedIndex) {
        let user = this.unAssignedUsers[unAssignedIndex];
        this.unAssignedUsers.splice(unAssignedIndex, 1);
        this.assignedUsers.push(user);
      },

      moveAllToAssigned() {
        let temp = this.unAssignedUsers;
        this.unAssignedUsers = [];
        temp.forEach((value, index) => {
          this.assignedUsers.push(value);
        });
      },

      moveAllToUnAssigned() {

      },

    },

  };
</script>

<style scoped>

</style>