from locust import HttpLocust, TaskSet, task

class WebsiteTasks(TaskSet):
    def on_start(self):   #进行初始化的工作，每个Locust用户开始做的第一件事
        
        payload = {
            "username": "test_user",
            "password": "123456",
        }
        

    @task(5)    #通过@task()装饰的方法为一个事务，方法的参数用于指定该行为的执行权重，参数越大每次被虚拟用户执行的概率越高，默认为1
    def index(self):
        payload = {
            "ctm_id":"117303",
            "hr_id":"1578858",
            "jobseek_id":"51897794",
            "resume_id":"301753187",
            "position":"导弹设计师",
            "sign_key":"623b255d79195b33332ade311ddf9b6d"
        }
        self.client.post("api/ehire/getresumeqrcodesingle?from_domain=ehire&union_id=11",data=payload)

class WebsiteUser(HttpLocust):
    host     = "http://192.168.7.35/" #被测系统的host，在终端中启动locust时没有指定--host参数时才会用到
    task_set = WebsiteTasks          #TaskSet类，该类定义用户任务信息，必填。这里就是:WebsiteTasks类名,因为该类继承TaskSet；
    min_wait = 1000  #每个用户执行两个任务间隔时间的上下限（毫秒）,具体数值在上下限中随机取值，若不指定默认间隔时间固定为1秒
    max_wait = 1000